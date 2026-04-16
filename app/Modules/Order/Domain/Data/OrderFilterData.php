<?php

declare(strict_types=1);

namespace App\Modules\Order\Domain\Data;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Order\Domain\Enums\OrderStatusEnum;
use Spatie\LaravelData\Data;

class OrderFilterData extends Data
{
    /**
     * @param  OrderStatusEnum[]|null  $statuses
     */
    public function __construct(
        public ?string $search = null,
        public ?MarketplaceEnum $marketplace = null,
        public ?array $statuses = null,
        public ?string $date_from = null,
        public ?string $date_to = null,
        public ?string $sort = null,
        public ?string $direction = null,
        public int $per_page = 15,
        public int $page = 1,
    ) {}
}
