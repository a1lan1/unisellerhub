<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Data;

class SyncMoySkladStockData
{
    public function __construct(
        public int $organizationId,
    ) {}
}
