<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Domain\Interfaces;

use Illuminate\Support\Collection;

interface MarketplaceClientInterface
{
    /**
     * Get stocks for all products or specific ones.
     */
    public function getStocks(array $options = []): Collection;

    /**
     * Get recent orders from the marketplace.
     */
    public function getOrders(array $options = []): Collection;

    /**
     * Get list of products available on the marketplace.
     */
    public function getProducts(array $options = []): Collection;

    /**
     * Update product prices on the marketplace.
     */
    public function updatePrices(Collection $prices): bool;

    /**
     * Update product stocks on the marketplace.
     */
    public function updateStocks(Collection $stocks): bool;
}
