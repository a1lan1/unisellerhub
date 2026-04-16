<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Domain\Repositories;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\MockMarketplace\Domain\Models\MockProduct;
use Illuminate\Support\Collection;

interface MockProductRepositoryInterface
{
    /**
     * @return Collection<int, MockProduct>
     */
    public function getProducts(int $accountId, MarketplaceEnum $marketplace): Collection;

    public function updatePrice(int $accountId, int $externalId, float $price): void;

    /**
     * @param  int[]  $productIds
     * @return Collection<int, MockProduct>
     */
    public function getProductsByIds(int $accountId, MarketplaceEnum $marketplace, array $productIds): Collection;

    public function updateOzonPrice(int $accountId, string $offerId, float $price): void;

    public function updateYandexPrice(int $accountId, string $offerId, float $price): void;

    public function findProduct(int $accountId, MarketplaceEnum $marketplace, int $externalId): ?MockProduct;

    public function updateAvitoPrice(int $accountId, int $externalId, float $price): void;

    /**
     * @return Collection<int, MockProduct>
     */
    public function getMsAssortment(int $accountId): Collection;
}
