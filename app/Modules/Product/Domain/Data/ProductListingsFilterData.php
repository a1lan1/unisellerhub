<?php

declare(strict_types=1);

namespace App\Modules\Product\Domain\Data;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use Spatie\LaravelData\Data;

class ProductListingsFilterData extends Data
{
    public function __construct(
        public ?string $search = null,
        public ?string $semanticSearch = null,
        public ?MarketplaceEnum $marketplace = null,
        public ?string $sort = null,
        public ?string $direction = null,
        public int $per_page = 15,
        public int $page = 1,
    ) {}
}
