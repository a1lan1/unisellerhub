<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Domain\Data;

use Spatie\LaravelData\Data;

class OzonMockProductListDTO extends Data
{
    public function __construct(
        public int $product_id,
        public string $offer_id,
    ) {}
}
