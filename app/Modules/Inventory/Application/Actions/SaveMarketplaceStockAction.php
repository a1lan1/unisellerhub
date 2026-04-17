<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Actions;

use App\Modules\Inventory\Domain\Data\StockData;
use App\Modules\Inventory\Domain\Events\InventorySynced;
use App\Modules\Inventory\Domain\Repositories\InventoryRepositoryInterface;
use App\Modules\Inventory\Domain\Repositories\WarehouseRepositoryInterface;
use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use App\Modules\Product\Domain\Models\ProductListing;
use App\Modules\Product\Domain\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

final readonly class SaveMarketplaceStockAction
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private WarehouseRepositoryInterface $warehouseRepository,
        private InventoryRepositoryInterface $inventoryRepository
    ) {}

    /**
     * @param  iterable<StockData>  $stocks
     *
     * @throws Throwable
     */
    public function execute(MarketplaceConnection $connection, iterable $stocks): void
    {
        DB::transaction(function () use ($connection, $stocks): void {
            foreach ($stocks as $stockData) {
                $this->saveStock($connection, $stockData);
            }
        });

        event(new InventorySynced($connection->organization_id));
    }

    private function saveStock(MarketplaceConnection $connection, StockData $stockData): void
    {
        // 1. Find product listing within the organization
        // Try by external_id first
        $listing = $this->productRepository->findListingByExternalId($connection->marketplace, $stockData->external_product_id);

        // Fallback to searching by vendor_code if external_id search fails
        if (! $listing instanceof ProductListing && ! in_array($stockData->sku, [null, '', '0'], true)) {
            $listing = $this->productRepository->findListingByVendorCode($connection->marketplace, $stockData->sku);
        }

        if (! $listing instanceof ProductListing) {
            Log::warning(sprintf('No matching ProductListing found for stockData: external_product_id=%s, sku=%s for %s. Skipping.', $stockData->external_product_id, $stockData->sku, $connection->marketplace->value));

            return;
        }

        // 2. Find or create warehouse
        $warehouse = $this->warehouseRepository->updateOrCreate(
            [
                'organization_id' => $connection->organization_id,
                'marketplace' => $connection->marketplace,
                'external_id' => $stockData->external_warehouse_id,
            ],
            [
                'name' => sprintf('%s Warehouse %s', $connection->marketplace->label(), $stockData->external_warehouse_id),
            ]
        );

        // 3. Update stock
        $this->inventoryRepository->updateOrCreate(
            [
                'product_listing_id' => $listing->id,
                'warehouse_id' => $warehouse->id,
            ],
            [
                'quantity' => $stockData->quantity,
            ]
        );

        // Update sync timestamp for listing
        $this->productRepository->updateListing($listing, ['last_synced_at' => now()]);
    }
}
