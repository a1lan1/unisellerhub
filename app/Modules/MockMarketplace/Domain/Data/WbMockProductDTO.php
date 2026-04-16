<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Domain\Data;

use Spatie\LaravelData\Data;

class WbMockProductDTO extends Data
{
    public function __construct(
        public int $nmId,
        public string $vendorCode,
        public string $title,
        public string $description,
        public string $brand,
        public array $photos,
        public array $characteristics,
        public string $subjectName,
        public array $sizes,
        public int $price = 0,
    ) {}
}
