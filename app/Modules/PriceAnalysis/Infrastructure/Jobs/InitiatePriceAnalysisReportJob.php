<?php

declare(strict_types=1);

namespace App\Modules\PriceAnalysis\Infrastructure\Jobs;

use App\Modules\PriceAnalysis\Application\Services\PriceAnalyzeAction;
use App\Modules\PriceAnalysis\Domain\Data\PriceAnalysisTaskData;
use App\Modules\PriceAnalysis\Domain\Repositories\PriceAnalysisRepositoryInterface;
use App\Modules\Product\Domain\Models\Product;
use App\Modules\Shared\Application\Services\TenantManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

#[Backoff(5)]
#[Tries(3)]
final class InitiatePriceAnalysisReportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public string $batchId;

    public function __construct(
        public int $organizationId,
        public int $userId,
    ) {
        $this->batchId = (string) Str::uuid();
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return $this->batchId;
    }

    public function handle(
        PriceAnalysisRepositoryInterface $repository,
        PriceAnalyzeAction $priceAnalysisService,
        TenantManager $tenantManager
    ): void {
        $tenantManager->setOrganizationId($this->organizationId);

        $products = Product::query()
            ->where('organization_id', $this->organizationId)
            ->with('listings.inventory')
            ->get();

        if ($products->isEmpty()) {
            Log::info('No products found for organization to initiate price analysis report.', ['organization_id' => $this->organizationId]);

            return;
        }

        $productListingIds = [];
        // Collect all product listing IDs first
        foreach ($products as $product) {
            // The previous checks for $product->id are sufficient.
            if (! $product->id) {
                Log::warning('Product found without an ID, skipping.', ['product' => $product->toArray()]);

                continue;
            }

            foreach ($product->listings as $listing) {
                // The previous checks for $listing->id are sufficient.
                if (! $listing->id) {
                    Log::warning('Product listing found without an ID, skipping.', ['product_id' => $product->id, 'listing' => $listing->toArray()]);

                    continue;
                }

                $productListingIds[] = $listing->id;
            }
        }

        if ($productListingIds === []) {
            Log::info('No valid product listings found with IDs to process for price analysis report.', ['organization_id' => $this->organizationId, 'batch_id' => $this->batchId]);

            return;
        }

        // Fetch all sales history in one go
        $allSalesHistory = $repository->getSalesHistoryForProductListings($productListingIds);

        // Group sales history by product_listing_id for easy access
        $salesHistoryByListing = $allSalesHistory->groupBy('product_listing_id');

        $batchDataForPriceAnalyzer = [];
        foreach ($products as $product) {
            // Re-check product ID in case it was skipped earlier
            if (! $product->id) {
                continue;
            }

            foreach ($product->listings as $listing) {
                // Re-check listing ID in case it was skipped earlier
                if (! $listing->id) {
                    continue;
                }

                $currentStock = $listing->inventory->sum('quantity');

                // Get sales history for the current listing
                $salesHistoryForThisListing = $salesHistoryByListing->get($listing->id, collect());

                $salesHistoryFormatted = $salesHistoryForThisListing->map(fn (array $item): array => [
                    'date' => $item['date'],
                    'quantity' => $item['quantity'],
                ])->all();

                $batchDataForPriceAnalyzer[] = new PriceAnalysisTaskData(
                    organization_id: $this->organizationId,
                    sku: $product->sku,
                    current_stock: $currentStock,
                    sales_history: $salesHistoryFormatted,
                    marketplace: $listing->marketplace->value,
                    product_id: $product->id,
                    batch_id: $this->batchId,
                );
            }
        }

        if ($batchDataForPriceAnalyzer === []) {
            Log::info('No valid product listings found to process for price analysis report after sales history aggregation.', ['organization_id' => $this->organizationId, 'batch_id' => $this->batchId]);

            return;
        }

        // Store batch metadata in Redis
        Redis::set(
            'price_analysis:batch:'.$this->batchId,
            json_encode([
                'organization_id' => $this->organizationId,
                'user_id' => $this->userId,
                'total_products_count' => count($batchDataForPriceAnalyzer),
                'status' => 'pending',
                'created_at' => now()->toIso8601String(),
            ])
        );

        // Send the entire batch to price_analyzer
        $priceAnalysisService->execute($batchDataForPriceAnalyzer);

        // All items in the batch should have the same batch_id and organization_id
        $firstItem = $batchDataForPriceAnalyzer[0];
        Log::info('Price analysis batch task successfully forwarded to microservice queue.', [
            'batch_id' => $firstItem->batch_id,
            'organization_id' => $firstItem->organization_id,
            'items_count' => count($batchDataForPriceAnalyzer),
        ]);
    }
}
