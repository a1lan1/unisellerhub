<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Repositories;

use App\Modules\Inventory\Domain\Data\InventoryFilterData;
use App\Modules\Inventory\Domain\Models\Inventory;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface InventoryRepositoryInterface
{
    public function getAllInventory(): Collection;

    public function getPaginatedInventory(InventoryFilterData $filter): LengthAwarePaginator;

    public function findById(int $id): ?Inventory;

    public function updateQuantity(Inventory $inventory, int $quantity): bool;

    public function updateOrCreate(array $lookup, array $data): Inventory;

    /**
     * Get inventory health statistics.
     */
    public function getHealthStats(): array;
}
