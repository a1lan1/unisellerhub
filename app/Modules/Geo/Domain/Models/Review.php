<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\Models;

use App\Modules\Geo\Domain\Data\ReviewFilterData;
use App\Modules\Geo\Domain\Enums\ReviewSourceEnum;
use App\Modules\Geo\Domain\Enums\SentimentEnum;
use App\Modules\Geo\Domain\Models\Builders\ReviewBuilder;
use App\Modules\Geo\Domain\Observers\ReviewObserver;
use App\Modules\Geo\Domain\Policies\ReviewPolicy;
use App\Modules\User\Domain\Models\User;
use Carbon\CarbonImmutable;
use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int $location_id
 * @property ReviewSourceEnum $source
 * @property string $external_id
 * @property string $author_name
 * @property string|null $text
 * @property int $rating
 * @property SentimentEnum|null $sentiment
 * @property CarbonImmutable $published_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Location $location
 *
 * @method static ReviewBuilder<static>|Review applyFilters(ReviewFilterData $filters)
 * @method static ReviewFactory factory($count = null, $state = [])
 * @method static ReviewBuilder<static>|Review forLocation(?int $locationId)
 * @method static ReviewBuilder<static>|Review forUser(User $user)
 * @method static ReviewBuilder<static>|Review newModelQuery()
 * @method static ReviewBuilder<static>|Review newQuery()
 * @method static ReviewBuilder<static>|Review query()
 * @method static ReviewBuilder<static>|Review whereAuthorName($value)
 * @method static ReviewBuilder<static>|Review whereCreatedAt($value)
 * @method static ReviewBuilder<static>|Review whereExternalId($value)
 * @method static ReviewBuilder<static>|Review whereId($value)
 * @method static ReviewBuilder<static>|Review whereLocationId($value)
 * @method static ReviewBuilder<static>|Review wherePublishedAt($value)
 * @method static ReviewBuilder<static>|Review whereRating($value)
 * @method static ReviewBuilder<static>|Review whereSentiment($value)
 * @method static ReviewBuilder<static>|Review whereSource($value)
 * @method static ReviewBuilder<static>|Review whereText($value)
 * @method static ReviewBuilder<static>|Review whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[ObservedBy([ReviewObserver::class])]
#[UseEloquentBuilder(ReviewBuilder::class)]
#[Fillable(['location_id', 'source', 'external_id', 'author_name', 'text', 'rating', 'sentiment', 'published_at'])]
#[UseFactory(ReviewFactory::class)]
#[UsePolicy(ReviewPolicy::class)]
class Review extends Model
{
    use HasFactory;

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'source' => ReviewSourceEnum::class,
            'sentiment' => SentimentEnum::class,
            'published_at' => 'datetime',
            'rating' => 'integer',
        ];
    }
}
