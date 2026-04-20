<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Infrastructure\Repositories;

use App\Modules\Inventory\Domain\Data\InventoryFilterData;
use App\Modules\Inventory\Domain\Models\Inventory;
use App\Modules\Inventory\Domain\Repositories\InventoryRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentInventoryRepository implements InventoryRepositoryInterface
{
    public function getPaginatedInventory(InventoryFilterData $filter): LengthAwarePaginator
    {
        return Inventory::query()
            ->whereHas('warehouse')
            ->with(['listing.product', 'warehouse'])
            ->when($filter->marketplace, fn (Builder $q, $m) => $q->whereHas('warehouse', fn (Builder $wq) => $wq->where('marketplace', $m)))
            ->when($filter->search, function (Builder $q, $s): void {
                $q->where(function (Builder $sq) use ($s): void {
                    $sq->whereHas('listing', fn (Builder $lq) => $lq->where('vendor_code', 'like', sprintf('%%%s%%', $s)))
                        ->orWhereHas('listing.product', fn (Builder $pq) => $pq->where('name', 'like', sprintf('%%%s%%', $s)));
                });
            })
            ->when($filter->sort, function (Builder $q, $s) use ($filter): void {
                $direction = $filter->direction ?? 'asc';
                match ($s) {
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
                    default => $q->orderBy($s, $direction),
                };
            }, fn (Builder $q) => $q->orderBy('id', 'desc'))
            ->paginate($filter->per_page, ['*'], 'page', $filter->page);
    }

    public function findById(int $id): ?Inventory
    {
        return Inventory::find($id);
    }

    public function updateQuantity(Inventory $inventory, int $quantity): bool
    {
        return $inventory->update(['quantity' => $quantity]);
    }

    public function updateOrCreate(array $lookup, array $data): Inventory
    {
        return Inventory::updateOrCreate($lookup, $data);
    }

    public function getHealthStats(): array
    {
        return [
            'out_of_stock' => Inventory::where('quantity', '<=', 0)->count(),
            'low_stock' => Inventory::where('quantity', '>', 0)->where('quantity', '<', 10)->count(),
            'total_items' => Inventory::count(),
        ];
    }
}
