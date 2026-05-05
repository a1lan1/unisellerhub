<?php

declare(strict_types=1);

namespace App\Modules\Product\Infrastructure\Repositories;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Product\Domain\Data\ProductListingsFilterData;
use App\Modules\Product\Domain\Data\ProductListingStoreData;
use App\Modules\Product\Domain\Models\ProductListing;
use App\Modules\Product\Domain\Repositories\ProductListingRepositoryInterface;
use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentProductListingRepository implements ProductListingRepositoryInterface
{
    /**
     * @param  array<int, MarketplaceEnum>  $marketplaces
     * @return Collection<int, ProductListing>
     */
    public function getByOrganizationAndMarketplaces(int $organizationId, array $marketplaces, string $vendorCode): Collection
    {
        return ProductListing::query()
            ->forOrganization($organizationId)
            ->whereIn('marketplace', $marketplaces)
            ->where('vendor_code', $vendorCode)
            ->get();
    }

    /**
     * @param  array<int, MarketplaceEnum>  $marketplaces
     * @param  array<int, string>  $skus
     * @return Collection<int, ProductListing>
     */
    public function getByOrganizationMarketplacesAndSkus(int $organizationId, array $marketplaces, array $skus): Collection
    {
        return ProductListing::query()
            ->forOrganization($organizationId)
            ->whereIn('marketplace', $marketplaces)
            ->whereIn('vendor_code', $skus)
            ->get();
    }

    /**
     * @param  array<int, int>  $ids
     * @return Collection<int, ProductListing>
     */
    public function getByIdsAndOrganization(array $ids, int $organizationId): Collection
    {
        return ProductListing::query()
            ->forOrganization($organizationId)
            ->whereIn('id', $ids)
            ->get();
    }

    /**
     * @return Collection<int, ProductListing>
     */
    public function getForInventoryExport(int $organizationId): Collection
    {
        return ProductListing::query()
            ->forOrganization($organizationId)
            ->with(['product', 'inventory.warehouse'])
            ->get();
    }

    public function getPaginatedListings(ProductListingsFilterData $filter): LengthAwarePaginator
    {
        return ProductListing::query()
            ->whereHas('product')
            ->with('product')
            ->filter($filter)
            ->paginate($filter->per_page, ['*'], 'page', $filter->page);
    }

    public function findListingByExternalId(MarketplaceEnum $marketplace, string $externalId): ?ProductListing
    {
        return ProductListing::query()
            ->forMarketplace($marketplace)
            ->where('external_id', $externalId)
            ->first();
    }

    public function findListingByVendorCode(MarketplaceEnum $marketplace, string $vendorCode): ?ProductListing
    {
        return ProductListing::query()
            ->forMarketplace($marketplace)
            ->where('vendor_code', $vendorCode)
            ->first();
    }

    public function updateOrCreate(ProductListingStoreData $productListingData): ProductListing
    {
        return ProductListing::updateOrCreate(
            [
                'external_id' => $productListingData->external_id,
                'marketplace' => $productListingData->marketplace,
            ],
            $productListingData->toArray()
        );
    }

    public function updateListing(ProductListing $listing, array $data): ProductListing
    {
        $listing->update($data);

        return $listing;
    }

    public function findListingById(int $id): ?ProductListing
    {
        return ProductListing::query()
            ->with('product')
            ->find($id);
    }

    public function updateFinance(int $listingId, array $listingData, ?Money $costPrice): ProductListing
    {
        $listing = ProductListing::query()
            ->with('product')
            ->findOrFail($listingId);

        $listing->update($listingData);

        if ($costPrice instanceof Money) {
            $listing->product->update(['cost_price' => $costPrice]);
        }

        return $listing;
    }
}
