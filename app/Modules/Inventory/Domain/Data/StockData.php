<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Data;

use Spatie\LaravelData\Data;

class StockData extends Data
{
    public function __construct(
        public string $external_product_id,
        public string $external_warehouse_id,
        public int $quantity,
        public ?string $sku = null,
    ) {}
}
