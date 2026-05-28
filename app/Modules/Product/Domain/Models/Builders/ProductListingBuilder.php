<?php

declare(strict_types=1);

namespace App\Modules\Product\Domain\Models\Builders;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Product\Domain\Data\ProductListingsFilterData;
use App\Modules\Product\Domain\Models\ProductListing;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModelClass of ProductListing
 *
 * @extends Builder<TModelClass>
 */
class ProductListingBuilder extends Builder
{
    public function forOrganization(int $organizationId): self
    {
        return $this->whereHas('product', fn (Builder $query) => $query->where('organization_id', $organizationId));
    }

    public function forMarketplace(MarketplaceEnum $marketplace): self
    {
        return $this->where('marketplace', $marketplace);
    }

    public function filter(ProductListingsFilterData $filter): self
    {
        return $this->when($filter->marketplace, fn (Builder $query, $marketplace) => $query->where('marketplace', $marketplace))
            ->when($filter->search, function (Builder $query, $s): void {
                $query->where(fn (Builder $sq) => $sq->where('vendor_code', 'like', sprintf('%%%s%%', $s))
                    ->orWhereHas('product', fn (Builder $pq) => $pq->where('name', 'like', sprintf('%%%s%%', $s))));
            })
            ->when($filter->semanticSearch, function (Builder $query, $semanticQuery): void {
                if (config('ai.vector_search.enabled')) {
                    $query->whereHas('product', function (Builder $pq) use ($semanticQuery): void {
                        $pq->whereVectorSimilarTo('embedding', $semanticQuery);
                    });
                }
            })
            ->when($filter->sortOrder, function (Builder $q, $sortOrder): void {
                $field = $sortOrder->getField();
                $direction = $sortOrder->getDirection();

                if ($field === 'name') {
                    $q->join('products', 'product_listings.product_id', '=', 'products.id')
                        ->select('product_listings.*')
                        ->orderBy('products.name', $direction);
                } else {
                    $q->orderBy($field, $direction);
                }
            }, fn (Builder $q) => $q->orderBy('id', 'desc'));
    }
}
