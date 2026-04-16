<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Domain\Data;

use Spatie\LaravelData\Data;

class WbMockStockDTO extends Data
{
    public function __construct(
        public string $sku,
        public int $amount,
        public int $warehouseId,
    ) {}
}
