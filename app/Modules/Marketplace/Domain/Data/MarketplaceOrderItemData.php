<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Domain\Data;

use Cknow\Money\Money;
use Spatie\LaravelData\Data;

class MarketplaceOrderItemData extends Data
{
    public function __construct(
        public string $product_id,
        public int $quantity,
        public Money $price,
        public ?string $sku = null,
    ) {}
}
