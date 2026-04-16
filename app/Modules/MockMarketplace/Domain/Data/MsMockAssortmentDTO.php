<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Domain\Data;

use Spatie\LaravelData\Data;

class MsMockAssortmentDTO extends Data
{
    public function __construct(
        public array $meta,
        public string $id,
        public string $name,
        public string $code,
        public string $externalCode,
        public string $article,
        public array $salePrices,
        public array $barcodes,
    ) {}
}
