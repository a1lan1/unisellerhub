<?php

declare(strict_types=1);

namespace App\Modules\Order\Domain\Data;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Order\Domain\Enums\OrderStatusEnum;
use App\Modules\Shared\Domain\ValueObjects\DateRange;
use App\Modules\Shared\Domain\ValueObjects\Pagination;
use App\Modules\Shared\Domain\ValueObjects\SortOrder;
use Spatie\LaravelData\Data;

class OrderFilterData extends Data
{
    /**
     * @param  OrderStatusEnum[]|null  $statuses
     */
    public function __construct(
        public Pagination $pagination,
        public ?string $search = null,
        public ?MarketplaceEnum $marketplace = null,
        public ?array $statuses = null,
        public ?DateRange $dateRange = null,
        public ?SortOrder $sortOrder = null,
    ) {}
}
