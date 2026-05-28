<?php

declare(strict_types=1);

namespace App\Modules\Product\Domain\Data;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Shared\Domain\ValueObjects\Pagination;
use App\Modules\Shared\Domain\ValueObjects\SortOrder;
use Spatie\LaravelData\Data;

class ProductListingsFilterData extends Data
{
    public function __construct(
        public ?string $search = null,
        public ?string $semanticSearch = null,
        public ?MarketplaceEnum $marketplace = null,
        public ?SortOrder $sortOrder = null,
        public ?Pagination $pagination = null,
    ) {}
}
