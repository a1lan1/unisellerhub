<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Domain\Repositories;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\MockMarketplace\Domain\Models\MockStock;
use Illuminate\Support\Collection;

interface MockStockRepositoryInterface
{
    /**
     * @return Collection<int, MockStock>
     */
    public function getStocks(int $accountId, MarketplaceEnum $marketplace, ?int $warehouseId = null): Collection;

    public function updateQuantity(int $accountId, string $warehouseId, string $sku, int $quantity): void;

    /**
     * @param  int[]  $productIds
     * @return Collection<int, MockStock>
     */
    public function getStocksByProductIds(int $accountId, MarketplaceEnum $marketplace, array $productIds): Collection;

    public function updateOzonQuantity(int $accountId, string $sku, int $quantity): void;

    public function updateYandexQuantity(int $accountId, string $sku, int $quantity): void;

    /**
     * @return Collection<int, MockStock>
     */
    public function getMsStocks(int $accountId): Collection;
}
