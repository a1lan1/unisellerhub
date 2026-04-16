<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Domain\Data;

use Spatie\LaravelData\Data;

class YandexMockProductDTO extends Data
{
    public function __construct(
        public array $offer,
        public array $mapping,
    ) {}
}
