<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Domain\Models;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use Carbon\CarbonImmutable;
use Database\Factories\MockMarketplace\MockProductFactory;
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
 * @property string|null $vendor_code
 * @property string|null $barcode
 * @property string $name
 * @property string|null $description
 * @property numeric $price
 * @property numeric|null $old_price
 * @property numeric $discount
 * @property string|null $brand
 * @property string|null $category
 * @property array<array-key, mixed>|null $images
 * @property array<array-key, mixed>|null $attributes
 * @property float|null $width
 * @property float|null $height
 * @property float|null $depth
 * @property float|null $weight
 * @property bool $is_active
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read MockMarketplaceAccount $mockMarketplaceAccount
 *
 * @method static MockProductFactory factory($count = null, $state = [])
 * @method static Builder<static>|MockProduct newModelQuery()
 * @method static Builder<static>|MockProduct newQuery()
 * @method static Builder<static>|MockProduct query()
 * @method static Builder<static>|MockProduct whereAttributes($value)
 * @method static Builder<static>|MockProduct whereBarcode($value)
 * @method static Builder<static>|MockProduct whereBrand($value)
 * @method static Builder<static>|MockProduct whereCategory($value)
 * @method static Builder<static>|MockProduct whereCreatedAt($value)
 * @method static Builder<static>|MockProduct whereDepth($value)
 * @method static Builder<static>|MockProduct whereDescription($value)
 * @method static Builder<static>|MockProduct whereDiscount($value)
 * @method static Builder<static>|MockProduct whereExternalId($value)
 * @method static Builder<static>|MockProduct whereHeight($value)
 * @method static Builder<static>|MockProduct whereId($value)
 * @method static Builder<static>|MockProduct whereImages($value)
 * @method static Builder<static>|MockProduct whereIsActive($value)
 * @method static Builder<static>|MockProduct whereMarketplace($value)
 * @method static Builder<static>|MockProduct whereMockMarketplaceAccountId($value)
 * @method static Builder<static>|MockProduct whereName($value)
 * @method static Builder<static>|MockProduct whereOldPrice($value)
 * @method static Builder<static>|MockProduct wherePrice($value)
 * @method static Builder<static>|MockProduct whereUpdatedAt($value)
 * @method static Builder<static>|MockProduct whereVendorCode($value)
 * @method static Builder<static>|MockProduct whereWeight($value)
 * @method static Builder<static>|MockProduct whereWidth($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'mock_marketplace_account_id',
    'marketplace',
    'external_id',
    'vendor_code',
    'barcode',
    'name',
    'description',
    'price',
    'old_price',
    'discount',
    'brand',
    'category',
    'images',
    'attributes',
    'width',
    'height',
    'depth',
    'weight',
    'is_active',
])]
#[UseFactory(MockProductFactory::class)]
class MockProduct extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'images' => 'array',
            'attributes' => 'array',
            'is_active' => 'boolean',
            'marketplace' => MarketplaceEnum::class,
            'width' => 'float',
            'height' => 'float',
            'depth' => 'float',
            'weight' => 'float',
        ];
    }

    public function mockMarketplaceAccount(): BelongsTo
    {
        return $this->belongsTo(MockMarketplaceAccount::class);
    }
}
