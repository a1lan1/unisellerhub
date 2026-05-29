<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Interfaces;

use App\Modules\Inventory\Domain\Data\InventoryFilterData;
use App\Modules\Inventory\Domain\Data\PullBulkInventoryData;
use App\Modules\Inventory\Domain\Data\PullInventoryData;
use App\Modules\Inventory\Domain\Data\SyncMoySkladStockData;
use App\Modules\Inventory\Domain\Models\Inventory;
use App\Modules\User\Domain\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface InventoryServiceInterface
{
    public function getPaginatedInventory(User $user, InventoryFilterData $filter): LengthAwarePaginator;

    public function getInventoryHealthStats(User $user): array;

    public function updateInventoryAndPushToMarketplace(int $inventoryId, int $quantity): Inventory;

    public function pullInventory(PullInventoryData $dto): void;

    public function pullBulkInventory(PullBulkInventoryData $dto): void;

    public function syncMoySkladStock(SyncMoySkladStockData $dto): void;
}
