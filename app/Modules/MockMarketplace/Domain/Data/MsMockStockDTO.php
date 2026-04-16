<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Domain\Data;

use Spatie\LaravelData\Data;

class MsMockStockDTO extends Data
{
    public function __construct(
        public float $stock,
        public float $reserve,
        public float $quantity,
        public string $name,
        public string $article,
    ) {}
}
