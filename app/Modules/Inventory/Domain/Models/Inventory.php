<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Models;

use App\Modules\Product\Domain\Models\ProductListing;
use Carbon\CarbonImmutable;
use Database\Factories\InventoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $product_listing_id
 * @property int $warehouse_id
 * @property int $quantity
 * @property int $reserved
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read ProductListing $listing
 * @property-read Warehouse $warehouse
 *
 * @method static InventoryFactory factory($count = null, $state = [])
 * @method static Builder<static>|Inventory newModelQuery()
 * @method static Builder<static>|Inventory newQuery()
 * @method static Builder<static>|Inventory query()
 * @method static Builder<static>|Inventory whereCreatedAt($value)
 * @method static Builder<static>|Inventory whereId($value)
 * @method static Builder<static>|Inventory whereProductListingId($value)
 * @method static Builder<static>|Inventory whereQuantity($value)
 * @method static Builder<static>|Inventory whereReserved($value)
 * @method static Builder<static>|Inventory whereUpdatedAt($value)
 * @method static Builder<static>|Inventory whereWarehouseId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['product_listing_id', 'warehouse_id', 'quantity', 'reserved'])]
#[Table(name: 'inventory')]
#[UseFactory(InventoryFactory::class)]
class Inventory extends Model
{
    use HasFactory;

    public function listing(): BelongsTo
    {
        return $this->belongsTo(ProductListing::class, 'product_listing_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
