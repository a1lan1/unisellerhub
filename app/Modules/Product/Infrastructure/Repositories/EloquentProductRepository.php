<?php

declare(strict_types=1);

namespace App\Modules\Product\Infrastructure\Repositories;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Product\Domain\Data\ProductListingsFilterData;
use App\Modules\Product\Domain\Models\Product;
use App\Modules\Product\Domain\Models\ProductListing;
use App\Modules\Product\Domain\Repositories\ProductRepositoryInterface;
use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function getAllListings(): Collection
    {
        return ProductListing::query()
            ->with('product')
            ->get();
    }

    public function getPaginatedListings(ProductListingsFilterData $filter): LengthAwarePaginator
    {
        return ProductListing::query()
            ->whereHas('product')
            ->with('product')
            ->when($filter->marketplace, fn (Builder $q, $m) => $q->where('marketplace', $m))
            ->when($filter->search, function (Builder $q, $s): void {
                $q->where(fn (Builder $sq) => $sq->where('vendor_code', 'like', sprintf('%%%s%%', $s))
                    ->orWhereHas('product', fn (Builder $pq) => $pq->where('name', 'like', sprintf('%%%s%%', $s))));
            })
            ->when($filter->sort, function (Builder $q, $s) use ($filter): void {
                $direction = $filter->direction ?? 'asc';
                if ($s === 'product_name') {
                    $q->join('products', 'product_listings.product_id', '=', 'products.id')
                        ->select('product_listings.*')
                        ->orderBy('products.name', $direction);
                } else {
                    $q->orderBy($s, $direction);
                }
            }, fn (Builder $q) => $q->orderBy('id', 'desc'))
            ->paginate($filter->per_page, ['*'], 'page', $filter->page);
    }

    public function findBySku(string $sku): ?Product
    {
        return Product::query()
            ->where('sku', $sku)
            ->first();
    }

    public function createProduct(array $data): Product
    {
        return Product::create($data);
    }

    public function updateProduct(Product $product, array $data): Product
    {
        $product->update($data);

        return $product;
    }

    public function findListingByExternalId(MarketplaceEnum $marketplace, string $externalId): ?ProductListing
    {
        return ProductListing::query()
            ->where('marketplace', $marketplace)
            ->where('external_id', $externalId)
            ->first();
    }

    public function findListingByVendorCode(MarketplaceEnum $marketplace, string $vendorCode): ?ProductListing
    {
        return ProductListing::query()
            ->where('marketplace', $marketplace)
            ->where('vendor_code', $vendorCode)
            ->first();
    }

    public function createListing(array $data): ProductListing
    {
        return ProductListing::create($data);
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
