<?php

declare(strict_types=1);

namespace App\Modules\Order\Domain\Models;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Order\Domain\Casts\ExternalOrderIdCast;
use App\Modules\Order\Domain\Data\OrderFilterData;
use App\Modules\Order\Domain\Enums\OrderStatusEnum;
use App\Modules\Order\Domain\Models\Builders\OrderBuilder;
use App\Modules\Order\Domain\ValueObjects\ExternalOrderId;
use App\Modules\Shared\Domain\Enums\QueueNameEnum;
use App\Modules\User\Domain\Models\Organization;
use App\Modules\User\Domain\Scopes\UserOrganizationScope;
use App\Observers\OrganizationIdObserver;
use Carbon\CarbonImmutable;
use Cknow\Money\Casts\MoneyIntegerCast;
use Cknow\Money\Money;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use Override;

/**
 * @property int $id
 * @property int|null $organization_id
 * @property MarketplaceEnum $marketplace
 * @property ExternalOrderId $external_id
 * @property OrderStatusEnum $status
 * @property Money $total_price
 * @property CarbonImmutable $order_date
 * @property array<array-key, mixed>|null $delivery_info
 * @property CarbonImmutable|null $last_synced_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Collection<int, OrderItem> $items
 * @property-read int|null $items_count
 * @property-read Organization|null $organization
 *
 * @method static OrderFactory factory($count = null, $state = [])
 * @method static OrderBuilder<static>|Order filter(OrderFilterData $filter)
 * @method static OrderBuilder<static>|Order forDate(string $date)
 * @method static OrderBuilder<static>|Order forMarketplace(MarketplaceEnum $marketplace)
 * @method static OrderBuilder<static>|Order forOrganization(int $organizationId)
 * @method static OrderBuilder<static>|Order newModelQuery()
 * @method static OrderBuilder<static>|Order newQuery()
 * @method static OrderBuilder<static>|Order query()
 * @method static OrderBuilder<static>|Order whereCreatedAt($value)
 * @method static OrderBuilder<static>|Order whereDeliveryInfo($value)
 * @method static OrderBuilder<static>|Order whereExternalId($value)
 * @method static OrderBuilder<static>|Order whereId($value)
 * @method static OrderBuilder<static>|Order whereLastSyncedAt($value)
 * @method static OrderBuilder<static>|Order whereMarketplace($value)
 * @method static OrderBuilder<static>|Order whereOrderDate($value)
 * @method static OrderBuilder<static>|Order whereOrganizationId($value)
 * @method static OrderBuilder<static>|Order whereStatus($value)
 * @method static OrderBuilder<static>|Order whereTotalPrice($value)
 * @method static OrderBuilder<static>|Order whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['marketplace', 'external_id', 'status', 'total_price', 'order_date', 'delivery_info', 'last_synced_at', 'organization_id'])]
#[ScopedBy([UserOrganizationScope::class])]
#[ObservedBy([OrganizationIdObserver::class])]
#[UseFactory(OrderFactory::class)]
#[UseEloquentBuilder(OrderBuilder::class)]
class Order extends Model
{
    use HasFactory;
    use Searchable;

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'external_id' => ExternalOrderIdCast::class,
            'delivery_info' => 'array',
            'order_date' => 'datetime',
            'last_synced_at' => 'datetime',
            'status' => OrderStatusEnum::class,
            'total_price' => MoneyIntegerCast::class,
            'marketplace' => MarketplaceEnum::class,
        ];
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (int) $this->id,
            'external_id' => $this->external_id->getValue(),
            'total_price' => $this->total_price->getAmount(),
            'organization_id' => (int) $this->organization_id,
        ];
    }

    /**
     * Modify the query used to retrieve models when making all of the models searchable.
     */
    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->withoutGlobalScopes();
    }

    /**
     * Get the queue that should be used for the searchable sync.
     */
    public function queueForSearchableSync(): string
    {
        return QueueNameEnum::MeilisearchTasks->value;
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
