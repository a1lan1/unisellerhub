<?php

declare(strict_types=1);

namespace App\Modules\Geo\Application\Services;

use App\Modules\Geo\Domain\Interfaces\GeoMetricServiceInterface;
use App\Modules\Geo\Domain\Models\Review;
use App\Modules\Geo\Domain\Repositories\ReviewRepositoryInterface;
use App\Modules\Shared\Domain\Enums\CacheKeyEnum;
use App\Modules\User\Domain\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;

class GeoMetricService implements GeoMetricServiceInterface
{
    public function __construct(protected ReviewRepositoryInterface $reviewRepository) {}

    /**
     * @return array<string, mixed>
     */
    public function calculateForUser(User $user, ?int $locationId = null): array
    {
        return Cache::tags(['reviews', 'locations'])->flexible(
            sprintf(CacheKeyEnum::GEO_METRICS_USER->value, $user->id).($locationId ?? 'all'),
            [Date::now()->addMinutes(15), Date::now()->addHour()],
            function () use ($user, $locationId): array {
                $reviews = $this->reviewRepository->getForUserAndLocation($user, $locationId);

                $averageRating = (float) $reviews->avg('rating');
                $totalReviews = $reviews->count();
                $sentimentCounts = $reviews->groupBy('sentiment')->map->count();
                $sourceCounts = $reviews->groupBy('source')->map->count();
                $ratingCounts = $reviews->groupBy('rating')->map->count();

                $ratingDynamics = $reviews
                    ->sortBy('published_at')
                    ->groupBy(fn (Review $review) => $review->published_at->format('Y-m-d'))
                    ->map(function (Collection $group): array {
                        /** @var Review $firstReview */
                        $firstReview = $group->first();
                        /** @var float $avgRating */
                        $avgRating = $group->avg('rating');

                        return [
                            'date' => $firstReview->published_at->format('Y-m-d'),
                            'average_rating' => $avgRating,
                        ];
                    })
                    ->values();

                return [
                    'average_rating' => $averageRating,
                    'total_reviews' => $totalReviews,
                    'sentiment_distribution' => $sentimentCounts,
                    'source_distribution' => $sourceCounts,
                    'rating_dynamics' => $ratingDynamics,
                    'rating_counts' => $ratingCounts,
                ];
            });
    }
}
