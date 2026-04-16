<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Domain\Data;

use Spatie\LaravelData\Data;

class WbMockOrderDTO extends Data
{
    public function __construct(
        public int $id,
        public string $status,
        public string $createdAt,
        public int $price,
        public array $items,
    ) {}
}
