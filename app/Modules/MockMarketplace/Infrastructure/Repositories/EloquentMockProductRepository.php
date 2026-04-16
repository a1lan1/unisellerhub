<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Infrastructure\Repositories;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\MockMarketplace\Domain\Models\MockProduct;
use App\Modules\MockMarketplace\Domain\Repositories\MockProductRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentMockProductRepository implements MockProductRepositoryInterface
{
    /**
     * @return Collection<int, MockProduct>
     */
    public function getProducts(int $accountId, MarketplaceEnum $marketplace): Collection
    {
        return MockProduct::where('mock_marketplace_account_id', $accountId)
            ->where('marketplace', $marketplace)
            ->get();
    }

    public function updatePrice(int $accountId, int $externalId, float $price): void
    {
        MockProduct::where('mock_marketplace_account_id', $accountId)
            ->where('external_id', $externalId)
            ->update(['price' => $price]);
    }

    /**
     * @param  int[]  $productIds
     * @return Collection<int, MockProduct>
     */
    public function getProductsByIds(int $accountId, MarketplaceEnum $marketplace, array $productIds): Collection
    {
        return MockProduct::where('mock_marketplace_account_id', $accountId)
            ->where('marketplace', $marketplace)
            ->whereIn('external_id', $productIds)
            ->get();
    }

    public function updateOzonPrice(int $accountId, string $offerId, float $price): void
    {
        MockProduct::where('mock_marketplace_account_id', $accountId)
            ->where('marketplace', MarketplaceEnum::OZON)
            ->where('vendor_code', $offerId)
            ->update(['price' => $price]);
    }

    public function updateYandexPrice(int $accountId, string $offerId, float $price): void
    {
        MockProduct::where('mock_marketplace_account_id', $accountId)
            ->where('marketplace', MarketplaceEnum::YANDEX)
            ->where('vendor_code', $offerId)
            ->update(['price' => $price]);
    }

    public function findProduct(int $accountId, MarketplaceEnum $marketplace, int $externalId): ?MockProduct
    {
        return MockProduct::where('mock_marketplace_account_id', $accountId)
            ->where('marketplace', $marketplace)
            ->where('external_id', (string) $externalId)
            ->first();
    }

    public function updateAvitoPrice(int $accountId, int $externalId, float $price): void
    {
        MockProduct::where('mock_marketplace_account_id', $accountId)
            ->where('external_id', (string) $externalId)
            ->update(['price' => $price]);
    }

    /**
     * @return Collection<int, MockProduct>
     */
    public function getMsAssortment(int $accountId): Collection
    {
        return MockProduct::where('mock_marketplace_account_id', $accountId)
            ->get();
    }
}
