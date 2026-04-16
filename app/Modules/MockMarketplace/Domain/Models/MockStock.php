<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Domain\Models;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use Carbon\CarbonImmutable;
use Database\Factories\MockMarketplace\MockStockFactory;
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
 * @property string $external_product_id
 * @property string|null $sku
 * @property string $external_warehouse_id
 * @property int $quantity
 * @property int $reserved
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read MockMarketplaceAccount $mockMarketplaceAccount
 *
 * @method static MockStockFactory factory($count = null, $state = [])
 * @method static Builder<static>|MockStock newModelQuery()
 * @method static Builder<static>|MockStock newQuery()
 * @method static Builder<static>|MockStock query()
 * @method static Builder<static>|MockStock whereCreatedAt($value)
 * @method static Builder<static>|MockStock whereExternalProductId($value)
 * @method static Builder<static>|MockStock whereExternalWarehouseId($value)
 * @method static Builder<static>|MockStock whereId($value)
 * @method static Builder<static>|MockStock whereMarketplace($value)
 * @method static Builder<static>|MockStock whereMockMarketplaceAccountId($value)
 * @method static Builder<static>|MockStock whereQuantity($value)
 * @method static Builder<static>|MockStock whereReserved($value)
 * @method static Builder<static>|MockStock whereSku($value)
 * @method static Builder<static>|MockStock whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['mock_marketplace_account_id', 'marketplace', 'external_product_id', 'sku', 'external_warehouse_id', 'quantity', 'reserved'])]
#[UseFactory(MockStockFactory::class)]
class MockStock extends Model
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

    public function mockMarketplaceAccount(): BelongsTo
    {
        return $this->belongsTo(MockMarketplaceAccount::class);
    }
}
