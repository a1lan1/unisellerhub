<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Domain\Data;

use Spatie\LaravelData\Data;

class AvitoMockStockDTO extends Data
{
    public function __construct(
        public int $item_id,
        public int $quantity,
        public string $warehouse_id,
    ) {}
}
