<?php

declare(strict_types=1);

namespace App\Modules\Order\Domain\Models;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\User\Domain\Models\Organization;
use App\Modules\User\Domain\Scopes\UserOrganizationScope;
use App\Observers\OrganizationIdObserver;
use Carbon\CarbonImmutable;
use Cknow\Money\Casts\MoneyIntegerCast;
use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use Override;

/**
 * @property int $id
 * @property int|null $organization_id
 * @property MarketplaceEnum $marketplace
 * @property string $external_id
 * @property string $status
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
 * @method static Builder<static>|Order newModelQuery()
 * @method static Builder<static>|Order newQuery()
 * @method static Builder<static>|Order query()
 * @method static Builder<static>|Order whereCreatedAt($value)
 * @method static Builder<static>|Order whereDeliveryInfo($value)
 * @method static Builder<static>|Order whereExternalId($value)
 * @method static Builder<static>|Order whereId($value)
 * @method static Builder<static>|Order whereLastSyncedAt($value)
 * @method static Builder<static>|Order whereMarketplace($value)
 * @method static Builder<static>|Order whereOrderDate($value)
 * @method static Builder<static>|Order whereOrganizationId($value)
 * @method static Builder<static>|Order whereStatus($value)
 * @method static Builder<static>|Order whereTotalPrice($value)
 * @method static Builder<static>|Order whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['marketplace', 'external_id', 'status', 'total_price', 'order_date', 'delivery_info', 'last_synced_at', 'organization_id'])]
#[ScopedBy([UserOrganizationScope::class])]
#[ObservedBy([OrganizationIdObserver::class])]
class Order extends Model
{
    use Searchable;

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'order_date' => 'datetime',
            'last_synced_at' => 'datetime',
            'total_price' => MoneyIntegerCast::class,
            'delivery_info' => 'array',
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
            'external_id' => (string) $this->external_id,
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

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
