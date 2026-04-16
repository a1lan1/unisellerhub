<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Domain\Data;

use Spatie\LaravelData\Data;

class AvitoMockOrderDTO extends Data
{
    public function __construct(
        public string $id,
        public string $status,
        public string $createdAt,
        public string $totalPrice,
        public array $items,
    ) {}
}
