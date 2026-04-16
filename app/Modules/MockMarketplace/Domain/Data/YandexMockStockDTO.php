<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Domain\Data;

use Spatie\LaravelData\Data;

class YandexMockStockDTO extends Data
{
    public function __construct(
        public string $offerId,
        public array $warehouseStocks,
    ) {}
}
