<?php

declare(strict_types=1);

namespace App\Modules\Product\Domain\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class ProductStoreData extends Data
{
    public function __construct(
        public string $sku,
        public string $name,
        public int $organizationId,
        public array $images = [],
        public array $attributes = [],
        public ?int $categoryId = null,
    ) {}
}
