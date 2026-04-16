<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Infrastructure\Repositories;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\MockMarketplace\Domain\Models\MockStock;
use App\Modules\MockMarketplace\Domain\Repositories\MockStockRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EloquentMockStockRepository implements MockStockRepositoryInterface
{
    /**
     * @return Collection<int, MockStock>
     */
    public function getStocks(int $accountId, MarketplaceEnum $marketplace, ?int $warehouseId = null): Collection
    {
        return MockStock::where('mock_marketplace_account_id', $accountId)
            ->where('marketplace', $marketplace)
            ->when($warehouseId, fn (Builder $q) => $q->where('external_warehouse_id', $warehouseId))
            ->get();
    }

    public function updateQuantity(int $accountId, string $warehouseId, string $sku, int $quantity): void
    {
        MockStock::where('mock_marketplace_account_id', $accountId)
            ->where('external_warehouse_id', $warehouseId)
            ->where('sku', $sku)
            ->update(['quantity' => $quantity]);
    }

    /**
     * @param  int[]  $productIds
     * @return Collection<int, MockStock>
     */
    public function getStocksByProductIds(int $accountId, MarketplaceEnum $marketplace, array $productIds): Collection
    {
        return MockStock::where('mock_marketplace_account_id', $accountId)
            ->where('marketplace', $marketplace)
            ->whereIn('external_product_id', $productIds)
            ->get();
    }

    public function updateOzonQuantity(int $accountId, string $sku, int $quantity): void
    {
        MockStock::where('mock_marketplace_account_id', $accountId)
            ->where('marketplace', MarketplaceEnum::OZON)
            ->where('sku', $sku)
            ->update(['quantity' => $quantity]);
    }

    public function updateYandexQuantity(int $accountId, string $sku, int $quantity): void
    {
        MockStock::where('mock_marketplace_account_id', $accountId)
            ->where('marketplace', MarketplaceEnum::YANDEX)
            ->where('sku', $sku)
            ->update(['quantity' => $quantity]);
    }

    /**
     * @return Collection<int, MockStock>
     */
    public function getMsStocks(int $accountId): Collection
    {
        return MockStock::where('mock_marketplace_account_id', $accountId)
            ->get();
    }
}
