<?php

declare(strict_types=1);

namespace App\Modules\Product\Domain\Models;

use App\Modules\Inventory\Domain\Models\Inventory;
use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use Carbon\CarbonImmutable;
use Cknow\Money\Casts\MoneyIntegerCast;
use Cknow\Money\Money;
use Database\Factories\ProductListingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property int $id
 * @property int $product_id
 * @property MarketplaceEnum $marketplace
 * @property string $external_id
 * @property string|null $vendor_code
 * @property Money|null $price
 * @property Money|null $old_price
 * @property int $discount
 * @property float $commission_percent
 * @property Money $logistic_cost
 * @property string $status
 * @property CarbonImmutable|null $last_synced_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Collection<int, Inventory> $inventory
 * @property-read int|null $inventory_count
 * @property-read Product $product
 *
 * @method static ProductListingFactory factory($count = null, $state = [])
 * @method static Builder<static>|ProductListing newModelQuery()
 * @method static Builder<static>|ProductListing newQuery()
 * @method static Builder<static>|ProductListing query()
 * @method static Builder<static>|ProductListing whereCommissionPercent($value)
 * @method static Builder<static>|ProductListing whereCreatedAt($value)
 * @method static Builder<static>|ProductListing whereDiscount($value)
 * @method static Builder<static>|ProductListing whereExternalId($value)
 * @method static Builder<static>|ProductListing whereId($value)
 * @method static Builder<static>|ProductListing whereLastSyncedAt($value)
 * @method static Builder<static>|ProductListing whereLogisticCost($value)
 * @method static Builder<static>|ProductListing whereMarketplace($value)
 * @method static Builder<static>|ProductListing whereOldPrice($value)
 * @method static Builder<static>|ProductListing wherePrice($value)
 * @method static Builder<static>|ProductListing whereProductId($value)
 * @method static Builder<static>|ProductListing whereStatus($value)
 * @method static Builder<static>|ProductListing whereUpdatedAt($value)
 * @method static Builder<static>|ProductListing whereVendorCode($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['product_id', 'marketplace', 'external_id', 'vendor_code', 'price', 'old_price', 'discount', 'commission_percent', 'logistic_cost', 'status', 'last_synced_at'])]
#[UseFactory(ProductListingFactory::class)]
class ProductListing extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
            'price' => MoneyIntegerCast::class,
            'old_price' => MoneyIntegerCast::class,
            'discount' => 'integer',
            'commission_percent' => 'float',
            'logistic_cost' => MoneyIntegerCast::class,
            'marketplace' => MarketplaceEnum::class,
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class, 'product_listing_id');
    }
}
