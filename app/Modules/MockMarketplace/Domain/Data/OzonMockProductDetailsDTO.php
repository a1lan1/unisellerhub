<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Domain\Data;

use Spatie\LaravelData\Data;

class OzonMockProductDetailsDTO extends Data
{
    public function __construct(
        public int $id,
        public string $offer_id,
        public string $name,
        public string $price,
        public ?string $old_price,
        public array $images,
        public string $barcode,
        public int $category_id,
        public string $description,
        public array $attributes,
    ) {}
}
