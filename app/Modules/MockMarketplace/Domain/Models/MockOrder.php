<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Domain\Models;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Order\Domain\Enums\OrderStatusEnum;
use Carbon\CarbonImmutable;
use Database\Factories\MockMarketplace\MockOrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int $mock_marketplace_account_id
 * @property MarketplaceEnum $marketplace
 * @property string $external_order_id
 * @property OrderStatusEnum $status
 * @property numeric $total_price
 * @property array<array-key, mixed> $items
 * @property array<array-key, mixed>|null $delivery_info
 * @property string $delivery_type
 * @property CarbonImmutable|null $shipment_date
 * @property CarbonImmutable $order_date
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read MockMarketplaceAccount $mockMarketplaceAccount
 *
 * @method static MockOrderFactory factory($count = null, $state = [])
 * @method static Builder<static>|MockOrder newModelQuery()
 * @method static Builder<static>|MockOrder newQuery()
 * @method static Builder<static>|MockOrder query()
 * @method static Builder<static>|MockOrder whereCreatedAt($value)
 * @method static Builder<static>|MockOrder whereDeliveryInfo($value)
 * @method static Builder<static>|MockOrder whereDeliveryType($value)
 * @method static Builder<static>|MockOrder whereExternalOrderId($value)
 * @method static Builder<static>|MockOrder whereId($value)
 * @method static Builder<static>|MockOrder whereItems($value)
 * @method static Builder<static>|MockOrder whereMarketplace($value)
 * @method static Builder<static>|MockOrder whereMockMarketplaceAccountId($value)
 * @method static Builder<static>|MockOrder whereOrderDate($value)
 * @method static Builder<static>|MockOrder whereShipmentDate($value)
 * @method static Builder<static>|MockOrder whereStatus($value)
 * @method static Builder<static>|MockOrder whereTotalPrice($value)
 * @method static Builder<static>|MockOrder whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'mock_marketplace_account_id',
    'marketplace',
    'external_order_id',
    'status',
    'total_price',
    'items',
    'delivery_info',
    'delivery_type',
    'shipment_date',
    'order_date',
])]
#[UseFactory(MockOrderFactory::class)]
class MockOrder extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'items' => 'array',
            'delivery_info' => 'array',
            'order_date' => 'datetime',
            'shipment_date' => 'datetime',
            'status' => OrderStatusEnum::class,
            'marketplace' => MarketplaceEnum::class,
        ];
    }

    public function mockMarketplaceAccount(): BelongsTo
    {
        return $this->belongsTo(MockMarketplaceAccount::class);
    }
}
