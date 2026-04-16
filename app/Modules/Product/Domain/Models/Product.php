<?php

declare(strict_types=1);

namespace App\Modules\Product\Domain\Models;

use App\Modules\User\Domain\Models\Organization;
use App\Modules\User\Domain\Scopes\UserOrganizationScope;
use App\Observers\OrganizationIdObserver;
use Carbon\CarbonImmutable;
use Cknow\Money\Casts\MoneyIntegerCast;
use Cknow\Money\Money;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
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
 * @property int $organization_id
 * @property string $sku
 * @property string $name
 * @property string|null $description
 * @property int|null $category_id
 * @property array<array-key, mixed>|null $images
 * @property array<array-key, mixed>|null $attributes
 * @property Money $cost_price
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Collection<int, ProductListing> $listings
 * @property-read int|null $listings_count
 * @property-read Organization $organization
 *
 * @method static ProductFactory factory($count = null, $state = [])
 * @method static Builder<static>|Product newModelQuery()
 * @method static Builder<static>|Product newQuery()
 * @method static Builder<static>|Product query()
 * @method static Builder<static>|Product whereAttributes($value)
 * @method static Builder<static>|Product whereCategoryId($value)
 * @method static Builder<static>|Product whereCostPrice($value)
 * @method static Builder<static>|Product whereCreatedAt($value)
 * @method static Builder<static>|Product whereDescription($value)
 * @method static Builder<static>|Product whereId($value)
 * @method static Builder<static>|Product whereImages($value)
 * @method static Builder<static>|Product whereName($value)
 * @method static Builder<static>|Product whereOrganizationId($value)
 * @method static Builder<static>|Product whereSku($value)
 * @method static Builder<static>|Product whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['sku', 'name', 'description', 'category_id', 'images', 'attributes', 'cost_price', 'organization_id'])]
#[UseFactory(ProductFactory::class)]
#[ScopedBy([UserOrganizationScope::class])]
#[ObservedBy([OrganizationIdObserver::class])]
class Product extends Model
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
            'images' => 'array',
            'attributes' => 'array',
            'cost_price' => MoneyIntegerCast::class,
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
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'organization_id' => $this->organization_id,
        ];
    }

    /**
     * Modify the query used to retrieve models when making all of the models searchable.
     */
    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->withoutGlobalScopes();
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function listings(): HasMany
    {
        return $this->hasMany(ProductListing::class);
    }
}
