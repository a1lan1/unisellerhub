<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Domain\Data;

use Spatie\LaravelData\Data;

class AvitoMockItemDTO extends Data
{
    public function __construct(
        public int $id,
        public string $title,
        public string $price,
        public string $status,
        public string $url,
    ) {}
}
