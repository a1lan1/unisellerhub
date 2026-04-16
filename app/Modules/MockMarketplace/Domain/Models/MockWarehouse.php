<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Domain\Models;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use Carbon\CarbonImmutable;
use Database\Factories\MockMarketplace\MockWarehouseFactory;
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
 * @property string $external_id
 * @property string $name
 * @property string|null $address
 * @property string|null $city
 * @property bool $is_active
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read MockMarketplaceAccount $mockMarketplaceAccount
 *
 * @method static MockWarehouseFactory factory($count = null, $state = [])
 * @method static Builder<static>|MockWarehouse newModelQuery()
 * @method static Builder<static>|MockWarehouse newQuery()
 * @method static Builder<static>|MockWarehouse query()
 * @method static Builder<static>|MockWarehouse whereAddress($value)
 * @method static Builder<static>|MockWarehouse whereCity($value)
 * @method static Builder<static>|MockWarehouse whereCreatedAt($value)
 * @method static Builder<static>|MockWarehouse whereExternalId($value)
 * @method static Builder<static>|MockWarehouse whereId($value)
 * @method static Builder<static>|MockWarehouse whereIsActive($value)
 * @method static Builder<static>|MockWarehouse whereMarketplace($value)
 * @method static Builder<static>|MockWarehouse whereMockMarketplaceAccountId($value)
 * @method static Builder<static>|MockWarehouse whereName($value)
 * @method static Builder<static>|MockWarehouse whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['mock_marketplace_account_id', 'marketplace', 'external_id', 'name', 'address', 'city', 'is_active'])]
#[UseFactory(MockWarehouseFactory::class)]
class MockWarehouse extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'marketplace' => MarketplaceEnum::class,
        ];
    }

    public function mockMarketplaceAccount(): BelongsTo
    {
        return $this->belongsTo(MockMarketplaceAccount::class);
    }
}
