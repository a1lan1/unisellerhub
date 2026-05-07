<?php

declare(strict_types=1);

namespace App\Modules\Product\Domain\Repositories;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Product\Domain\Data\ProductListingsFilterData;
use App\Modules\Product\Domain\Data\ProductListingStoreData;
use App\Modules\Product\Domain\Models\ProductListing;
use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductListingRepositoryInterface
{
    /**
     * @param  array<int, MarketplaceEnum>  $marketplaces
     * @return Collection<int, ProductListing>
     */
    public function getByOrganizationAndMarketplaces(int $organizationId, array $marketplaces, string $vendorCode): Collection;

    /**
     * @param  array<int, MarketplaceEnum>  $marketplaces
     * @param  array<int, string>  $skus
     * @return Collection<int, ProductListing>
     */
    public function getByOrganizationMarketplacesAndSkus(int $organizationId, array $marketplaces, array $skus): Collection;

    /**
     * @param  array<int, int>  $ids
     * @return Collection<int, ProductListing>
     */
    public function getByIdsAndOrganization(array $ids, int $organizationId): Collection;

    public function getPaginatedListings(ProductListingsFilterData $filter): LengthAwarePaginator;

    public function findListingByExternalId(MarketplaceEnum $marketplace, string $externalId): ?ProductListing;

    public function findListingByVendorCode(MarketplaceEnum $marketplace, string $vendorCode): ?ProductListing;

    public function updateOrCreate(ProductListingStoreData $productListingData): ProductListing;

    public function updateListing(ProductListing $listing, array $data): ProductListing;

    public function findListingById(int $id): ?ProductListing;

    /**
     * Update financial data for product and its listing.
     */
    public function updateFinance(int $listingId, array $listingData, ?Money $costPrice): ProductListing;
}
