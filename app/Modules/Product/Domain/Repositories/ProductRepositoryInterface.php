<?php

declare(strict_types=1);

namespace App\Modules\Product\Domain\Repositories;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Product\Domain\Data\ProductListingsFilterData;
use App\Modules\Product\Domain\Models\Product;
use App\Modules\Product\Domain\Models\ProductListing;
use Cknow\Money\Money;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ProductRepositoryInterface
{
    public function getAllListings(): Collection;

    public function getPaginatedListings(ProductListingsFilterData $filter): LengthAwarePaginator;

    public function findBySku(string $sku): ?Product;

    public function createProduct(array $data): Product;

    public function updateProduct(Product $product, array $data): Product;

    public function findListingByExternalId(MarketplaceEnum $marketplace, string $externalId): ?ProductListing;

    public function findListingByVendorCode(MarketplaceEnum $marketplace, string $vendorCode): ?ProductListing;

    public function createListing(array $data): ProductListing;

    public function updateListing(ProductListing $listing, array $data): ProductListing;

    public function findListingById(int $id): ?ProductListing;

    /**
     * Update financial data for product and its listing.
     */
    public function updateFinance(int $listingId, array $listingData, ?Money $costPrice): ProductListing;
}
