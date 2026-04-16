<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Infrastructure\Repositories;

use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Inventory\Domain\Repositories\WarehouseRepositoryInterface;

class EloquentWarehouseRepository implements WarehouseRepositoryInterface
{
    public function updateOrCreate(array $lookup, array $data): Warehouse
    {
        return Warehouse::updateOrCreate($lookup, $data);
    }
}
