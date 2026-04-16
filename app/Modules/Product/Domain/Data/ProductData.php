<?php

declare(strict_types=1);

namespace App\Modules\Product\Domain\Data;

use Cknow\Money\Money;
use Spatie\LaravelData\Data;

class ProductData extends Data
{
    public function __construct(
        public string $external_id,
        public string $vendor_code,
        public string $name,
        public Money $price,
        public ?Money $old_price = null,
        // public ?float $discount = null,
        public array $images = [],
        public array $attributes = [],
        public ?string $brand = null,
        public ?string $category = null,
    ) {}
}
