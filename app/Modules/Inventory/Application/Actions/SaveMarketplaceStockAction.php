<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Actions;

use App\Modules\Inventory\Domain\Data\StockData;
use App\Modules\Inventory\Domain\Events\InventorySynced;
use App\Modules\Inventory\Domain\Repositories\InventoryRepositoryInterface;
use App\Modules\Inventory\Domain\Repositories\WarehouseRepositoryInterface;
use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use App\Modules\Product\Domain\Models\ProductListing;
use App\Modules\Product\Domain\Repositories\ProductListingRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SaveMarketplaceStockAction
{
    public function __construct(
        private readonly ProductListingRepositoryInterface $productListingRepository,
        private readonly WarehouseRepositoryInterface $warehouseRepository,
        private readonly InventoryRepositoryInterface $inventoryRepository
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
        $listing = $this->productListingRepository->findListingByExternalId($connection->marketplace, $stockData->external_product_id->getValue());

        // Fallback to searching by vendor_code if external_id search fails
        if (! $listing instanceof ProductListing && ! in_array($stockData->sku?->getValue(), [null, '', '0'], true)) {
            $listing = $this->productListingRepository->findListingByVendorCode($connection->marketplace, $stockData->sku->getValue());
        }

        if (! $listing instanceof ProductListing) {
            Log::warning(sprintf('No matching ProductListing found for stockData: external_product_id=%s, sku=%s for %s. Skipping.', $stockData->external_product_id->getValue(), $stockData->sku?->getValue(), $connection->marketplace->value));

            return;
        }

        // 2. Find or create warehouse
        $warehouse = $this->warehouseRepository->updateOrCreate(
            [
                'organization_id' => $connection->organization_id,
                'marketplace' => $connection->marketplace,
                'external_id' => $stockData->external_warehouse_id->getValue(),
            ],
            [
                'name' => sprintf('%s Warehouse %s', $connection->marketplace->label(), $stockData->external_warehouse_id->getValue()),
            ]
        );

        // 3. Update stock
        $this->inventoryRepository->updateOrCreate(
            [
                'product_listing_id' => $listing->id,
                'warehouse_id' => $warehouse->id,
            ],
            [
                'quantity' => $stockData->quantity->getValue(),
            ]
        );

        // Update sync timestamp for listing
        $this->productListingRepository->updateListing($listing, ['last_synced_at' => now()]);
    }
}
