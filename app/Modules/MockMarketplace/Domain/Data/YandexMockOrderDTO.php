<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Domain\Data;

use Spatie\LaravelData\Data;

class YandexMockOrderDTO extends Data
{
    public function __construct(
        public int $id,
        public string $status,
        public string $creationDate,
        public array $items,
        public float $totalPrice,
    ) {}
}
