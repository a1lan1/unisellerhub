<?php

declare(strict_types=1);

namespace App\Modules\Order\Domain\Models;

use App\Modules\Inventory\Domain\Casts\QuantityCast;
use App\Modules\Inventory\Domain\ValueObjects\Quantity;
use App\Modules\Marketplace\Domain\Casts\MarketplaceProductIdCast;
use App\Modules\Marketplace\Domain\ValueObjects\MarketplaceProductId;
use App\Modules\Order\Domain\Models\Builders\OrderItemBuilder;
use App\Modules\Product\Domain\Models\ProductListing;
use Carbon\CarbonImmutable;
use Cknow\Money\Casts\MoneyIntegerCast;
use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int $order_id
 * @property int|null $product_listing_id
 * @property MarketplaceProductId $external_product_id
 * @property Quantity $quantity
 * @property Money $price
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read ProductListing|null $listing
 * @property-read Order $order
 *
 * @method static OrderItemBuilder<static>|OrderItem newModelQuery()
 * @method static OrderItemBuilder<static>|OrderItem newQuery()
 * @method static OrderItemBuilder<static>|OrderItem query()
 * @method static OrderItemBuilder<static>|OrderItem revenue(int $organizationId, string $endDate, int $days = 30)
 * @method static OrderItemBuilder<static>|OrderItem whereCreatedAt($value)
 * @method static OrderItemBuilder<static>|OrderItem whereExternalProductId($value)
 * @method static OrderItemBuilder<static>|OrderItem whereId($value)
 * @method static OrderItemBuilder<static>|OrderItem whereOrderId($value)
 * @method static OrderItemBuilder<static>|OrderItem wherePrice($value)
 * @method static OrderItemBuilder<static>|OrderItem whereProductListingId($value)
 * @method static OrderItemBuilder<static>|OrderItem whereQuantity($value)
 * @method static OrderItemBuilder<static>|OrderItem whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['order_id', 'product_listing_id', 'external_product_id', 'quantity', 'price'])]
#[UseEloquentBuilder(OrderItemBuilder::class)]
class OrderItem extends Model
{
    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'quantity' => QuantityCast::class,
            'external_product_id' => MarketplaceProductIdCast::class,
            'price' => MoneyIntegerCast::class,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(ProductListing::class, 'product_listing_id');
    }
}
