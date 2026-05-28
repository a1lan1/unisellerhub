<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Infrastructure\Repositories;

use App\Modules\Inventory\Domain\Data\InventoryFilterData;
use App\Modules\Inventory\Domain\Models\Inventory;
use App\Modules\Inventory\Domain\Repositories\InventoryRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentInventoryRepository implements InventoryRepositoryInterface
{
    public function getPaginatedInventory(InventoryFilterData $filter): LengthAwarePaginator
    {
        return Inventory::query()
            ->whereHas('warehouse')
            ->with(['listing.product', 'warehouse'])
            ->filter($filter)
            ->paginate($filter->pagination->getPerPage(), ['*'], 'page', $filter->pagination->getPage());
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
            'out_of_stock' => Inventory::outOfStock()->count(),
            'low_stock' => Inventory::lowStock()->count(),
            'total_items' => Inventory::count(),
        ];
    }

    public function getOutOfStockItems(): Collection
    {
        return Inventory::outOfStock()
            ->with(['listing.product', 'warehouse'])
            ->get();
    }

    public function getLowStockItems(): Collection
    {
        return Inventory::lowStock()
            ->with(['listing.product', 'warehouse'])
            ->get();
    }
}
