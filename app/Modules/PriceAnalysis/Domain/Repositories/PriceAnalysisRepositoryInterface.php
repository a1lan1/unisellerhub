<?php

declare(strict_types=1);

namespace App\Modules\PriceAnalysis\Domain\Repositories;

use App\Modules\Product\Domain\Models\Product;
use Illuminate\Support\Collection;

interface PriceAnalysisRepositoryInterface
{
    public function getProductById(int $productId, int $organizationId): ?Product;

    /**
     * @param  int|array<int>  $productListingIds
     * @return Collection<int, array{product_listing_id: int, date: string, quantity: int}>
     */
    public function getSalesHistoryForProductListings(int|array $productListingIds, int $days = 30): Collection;
}
