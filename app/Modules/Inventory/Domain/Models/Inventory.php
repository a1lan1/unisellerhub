<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Models;

use App\Modules\Inventory\Domain\Casts\QuantityCast;
use App\Modules\Inventory\Domain\Data\InventoryFilterData;
use App\Modules\Inventory\Domain\Models\Builders\InventoryBuilder;
use App\Modules\Inventory\Domain\ValueObjects\Quantity;
use App\Modules\Product\Domain\Models\ProductListing;
use Carbon\CarbonImmutable;
use Database\Factories\InventoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int $product_listing_id
 * @property int $warehouse_id
 * @property Quantity $quantity
 * @property Quantity $reserved
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read int $available
 * @property-read ProductListing $listing
 * @property-read Warehouse $warehouse
 *
 * @method static InventoryFactory factory($count = null, $state = [])
 * @method static InventoryBuilder<static>|Inventory filter(InventoryFilterData $filter)
 * @method static InventoryBuilder<static>|Inventory forDate(string $date)
 * @method static InventoryBuilder<static>|Inventory lowStock()
 * @method static InventoryBuilder<static>|Inventory newModelQuery()
 * @method static InventoryBuilder<static>|Inventory newQuery()
 * @method static InventoryBuilder<static>|Inventory outOfStock()
 * @method static InventoryBuilder<static>|Inventory query()
 * @method static InventoryBuilder<static>|Inventory whereCreatedAt($value)
 * @method static InventoryBuilder<static>|Inventory whereId($value)
 * @method static InventoryBuilder<static>|Inventory whereProductListingId($value)
 * @method static InventoryBuilder<static>|Inventory whereQuantity($value)
 * @method static InventoryBuilder<static>|Inventory whereReserved($value)
 * @method static InventoryBuilder<static>|Inventory whereUpdatedAt($value)
 * @method static InventoryBuilder<static>|Inventory whereWarehouseId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['product_listing_id', 'warehouse_id', 'quantity', 'reserved'])]
#[Table(name: 'inventory')]
#[UseFactory(InventoryFactory::class)]
#[UseEloquentBuilder(InventoryBuilder::class)]
class Inventory extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'quantity' => QuantityCast::class,
            'reserved' => QuantityCast::class,
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(ProductListing::class, 'product_listing_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    protected function available(): Attribute
    {
        return Attribute::make(
            get: fn (): int => $this->quantity->getValue() - $this->reserved->getValue(),
        );
    }
}
