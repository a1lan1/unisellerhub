<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\Models;

use App\Modules\Geo\Domain\Casts\AddressCast;
use App\Modules\Geo\Domain\Enums\LocationTypeEnum;
use App\Modules\Geo\Domain\Observers\LocationObserver;
use App\Modules\Geo\Domain\Policies\LocationPolicy;
use App\Modules\Shared\ValueObjects\Address;
use App\Modules\User\Domain\Models\User;
use Carbon\CarbonImmutable;
use Database\Factories\LocationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
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
 * @property int $user_id
 * @property string $name
 * @property LocationTypeEnum $type
 * @property Address|null $address
 * @property float $latitude
 * @property float $longitude
 * @property array<array-key, mixed>|null $external_ids
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Collection<int, Review> $reviews
 * @property-read int|null $reviews_count
 * @property-read User $seller
 *
 * @method static LocationFactory factory($count = null, $state = [])
 * @method static Builder<static>|Location newModelQuery()
 * @method static Builder<static>|Location newQuery()
 * @method static Builder<static>|Location query()
 * @method static Builder<static>|Location whereAddress($value)
 * @method static Builder<static>|Location whereCreatedAt($value)
 * @method static Builder<static>|Location whereExternalIds($value)
 * @method static Builder<static>|Location whereId($value)
 * @method static Builder<static>|Location whereLatitude($value)
 * @method static Builder<static>|Location whereLongitude($value)
 * @method static Builder<static>|Location whereName($value)
 * @method static Builder<static>|Location whereType($value)
 * @method static Builder<static>|Location whereUpdatedAt($value)
 * @method static Builder<static>|Location whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[ObservedBy([LocationObserver::class])]
#[UsePolicy(LocationPolicy::class)]
#[Fillable([
    'user_id',
    'name',
    'type',
    'address',
    'latitude',
    'longitude',
    'external_ids',
])]
#[UseFactory(LocationFactory::class)]
class Location extends Model
{
    use HasFactory;
    use Searchable;

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'type' => $this->type->value,
            'user_id' => (string) $this->user_id,
            'address_full' => $this->address->fullAddress,
            'address_city' => $this->address->city,
            'address_street' => $this->address->street,
            'address_postal_code' => $this->address->postalCode,
            'address_country' => $this->address->country,
            '_geo' => [
                'lat' => $this->latitude,
                'lng' => $this->longitude,
            ],
        ];
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'type' => LocationTypeEnum::class,
            'address' => AddressCast::class,
            'latitude' => 'float',
            'longitude' => 'float',
            'external_ids' => 'array',
        ];
    }
}
