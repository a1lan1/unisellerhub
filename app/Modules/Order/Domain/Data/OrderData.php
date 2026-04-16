<?php

declare(strict_types=1);

namespace App\Modules\Order\Domain\Data;

use App\Modules\Marketplace\Domain\Data\MarketplaceOrderItemData;
use Cknow\Money\Money;
use DateTimeInterface;
use Spatie\LaravelData\Data;

class OrderData extends Data
{
    /**
     * @param  MarketplaceOrderItemData[]  $items
     */
    public function __construct(
        public string $external_id,
        public string $status,
        public Money $total_price,
        public array $items,
        public DateTimeInterface $order_date,
        public ?array $delivery_info = null,
    ) {}
}
