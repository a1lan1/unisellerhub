<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Repositories;

use App\Modules\Inventory\Domain\Models\Warehouse;

interface WarehouseRepositoryInterface
{
    public function updateOrCreate(array $lookup, array $data): Warehouse;
}
