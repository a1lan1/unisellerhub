<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Data;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Shared\Domain\ValueObjects\Pagination;
use App\Modules\Shared\Domain\ValueObjects\SortOrder;
use Spatie\LaravelData\Data;

class InventoryFilterData extends Data
{
    public function __construct(
        public Pagination $pagination,
        public ?string $search = null,
        public ?MarketplaceEnum $marketplace = null,
        public ?SortOrder $sortOrder = null,
    ) {}
}
