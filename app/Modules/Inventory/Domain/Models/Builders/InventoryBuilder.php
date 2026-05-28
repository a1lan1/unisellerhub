<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Models\Builders;

use App\Modules\Inventory\Domain\Data\InventoryFilterData;
use App\Modules\Inventory\Domain\Models\Inventory;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModelClass of Inventory
 *
 * @extends Builder<TModelClass>
 */
class InventoryBuilder extends Builder
{
    public function filter(InventoryFilterData $filter): self
    {
        return $this->when($filter->marketplace, fn (Builder $q, $m) => $q->whereHas('warehouse', fn (Builder $wq) => $wq->where('marketplace', $m)))
            ->when($filter->search, function (Builder $q, $s): void {
                $q->where(function (Builder $sq) use ($s): void {
                    $sq->whereHas('listing', fn (Builder $lq) => $lq->where('vendor_code', 'like', sprintf('%%%s%%', $s)))
                        ->orWhereHas('listing.product', fn (Builder $pq) => $pq->where('name', 'like', sprintf('%%%s%%', $s)));
                });
            })
            ->when($filter->sortOrder, function (Builder $q, $sortOrder): void {
                $direction = $sortOrder->getDirection();
                match ($sortOrder->getField()) {
                    'product_name' => $q->join('product_listings', 'inventory.product_listing_id', '=', 'product_listings.id')
                        ->join('products', 'product_listings.product_id', '=', 'products.id')
                        ->select('inventory.*')
                        ->orderBy('products.name', $direction),
                    'marketplace' => $q->join('warehouses', 'inventory.warehouse_id', '=', 'warehouses.id')
                        ->select('inventory.*')
                        ->orderBy('warehouses.marketplace', $direction),
                    'warehouse_name' => $q->join('warehouses', 'inventory.warehouse_id', '=', 'warehouses.id')
                        ->select('inventory.*')
                        ->orderBy('warehouses.name', $direction),
                    'sku' => $q->join('product_listings', 'inventory.product_listing_id', '=', 'product_listings.id')
                        ->select('inventory.*')
                        ->orderBy('product_listings.vendor_code', $direction),
                    default => $q->orderBy('id', 'desc'),
                };
            }, fn (Builder $q) => $q->orderBy('id', 'desc'));
    }

    public function forDate(string $date): self
    {
        return $this->whereDate('order_date', $date);
    }

    public function outOfStock(): self
    {
        return $this->where('quantity', '<=', 0);
    }

    public function lowStock(): self
    {
        return $this->where('quantity', '>', 0)
            ->where('quantity', '<', 10);
    }
}
