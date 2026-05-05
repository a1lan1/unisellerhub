<?php

declare(strict_types=1);

namespace App\Modules\Product\Domain\Data;

use Cknow\Money\Money;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class ProductData extends Data
{
    public function __construct(
        public string $external_id,
        public string $vendor_code,
        public string $name,
        public Money $price,
        public ?Money $old_price = null,
        public array $images = [],
        public array $attributes = [],
        public ?string $brand = null,
        public ?string $category = null,
        public ?int $categoryId = null,
    ) {}
}
