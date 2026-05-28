<?php

declare(strict_types=1);

namespace App\Modules\Order\Domain\Data;

use App\Modules\Inventory\Domain\ValueObjects\Quantity;
use App\Modules\Marketplace\Domain\Data\MarketplaceOrderItemData;
use App\Modules\Marketplace\Domain\ValueObjects\MarketplaceProductId;
use App\Modules\Order\Domain\ValueObjects\ExternalOrderId;
use App\Modules\Product\ValueObjects\Sku;
use Cknow\Money\Money;
use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeInterface;
use Spatie\LaravelData\Data;

class OrderData extends Data
{
    /**
     * @param  MarketplaceOrderItemData[]  $items
     */
    public function __construct(
        public ExternalOrderId $external_id,
        public string $status,
        public Money $total_price,
        public array $items,
        public DateTimeInterface $order_date,
        public ?array $delivery_info = null,
    ) {}

    /**
     * @throws DateMalformedStringException
     */
    public static function fromArray(array $data): self
    {
        return new self(
            external_id: new ExternalOrderId((string) $data['external_id']),
            status: (string) $data['status'],
            total_price: Money::parse($data['total_price']), // Assuming total_price is a string like "100.00 USD"
            items: array_map(fn (array $itemData): MarketplaceOrderItemData => new MarketplaceOrderItemData(
                product_id: new MarketplaceProductId((string) ($itemData['product_id'] ?? '')),
                quantity: new Quantity((int) ($itemData['quantity'] ?? 0)),
                price: Money::parse($itemData['price']),
                sku: ($itemData['sku'] ?? null) ? new Sku((string) $itemData['sku']) : null,
            ), $data['items']),
            order_date: new DateTimeImmutable($data['order_date']),
            delivery_info: $data['delivery_info'] ?? null,
        );
    }
}
