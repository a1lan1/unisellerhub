<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Data;

use App\Modules\Inventory\Domain\ValueObjects\ExternalProductId;
use App\Modules\Inventory\Domain\ValueObjects\ExternalWarehouseId;
use App\Modules\Inventory\Domain\ValueObjects\Quantity;
use App\Modules\Product\ValueObjects\Sku;
use Spatie\LaravelData\Data;

class StockData extends Data
{
    public function __construct(
        public ExternalProductId $external_product_id,
        public ExternalWarehouseId $external_warehouse_id,
        public Quantity $quantity,
        public ?Sku $sku = null,
    ) {}
}
