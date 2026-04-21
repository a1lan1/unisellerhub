<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Domain\Models;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use Carbon\CarbonImmutable;
use Database\Factories\MockMarketplace\MockMarketplaceAccountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property int $id
 * @property MarketplaceEnum $marketplace
 * @property string $name
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Collection<int, MockMarketplaceCredential> $credentials
 * @property-read int|null $credentials_count
 * @property-read Collection<int, MockOrder> $orders
 * @property-read int|null $orders_count
 * @property-read Collection<int, MockProduct> $products
 * @property-read int|null $products_count
 * @property-read Collection<int, MockStock> $stocks
 * @property-read int|null $stocks_count
 * @property-read Collection<int, MockWarehouse> $warehouses
 * @property-read int|null $warehouses_count
 *
 * @method static MockMarketplaceAccountFactory factory($count = null, $state = [])
 * @method static Builder<static>|MockMarketplaceAccount newModelQuery()
 * @method static Builder<static>|MockMarketplaceAccount newQuery()
 * @method static Builder<static>|MockMarketplaceAccount query()
 * @method static Builder<static>|MockMarketplaceAccount whereCreatedAt($value)
 * @method static Builder<static>|MockMarketplaceAccount whereId($value)
 * @method static Builder<static>|MockMarketplaceAccount whereMarketplace($value)
 * @method static Builder<static>|MockMarketplaceAccount whereName($value)
 * @method static Builder<static>|MockMarketplaceAccount whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['marketplace', 'name'])]
#[UseFactory(MockMarketplaceAccountFactory::class)]
class MockMarketplaceAccount extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'marketplace' => MarketplaceEnum::class,
        ];
    }

    public function credentials(): HasMany
    {
        return $this->hasMany(MockMarketplaceCredential::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(MockProduct::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(MockOrder::class);
    }

    public function warehouses(): HasMany
    {
        return $this->hasMany(MockWarehouse::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(MockStock::class);
    }
}
