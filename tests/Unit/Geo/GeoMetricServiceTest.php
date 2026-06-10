<?php

declare(strict_types=1);

use App\Modules\Geo\Application\Services\GeoMetricService;
use App\Modules\Geo\Domain\Models\Review;
use App\Modules\Geo\Domain\Repositories\ReviewRepositoryInterface;
use App\Modules\Shared\Domain\Enums\CacheKeyEnum;
use App\Modules\User\Domain\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    $this->reviewRepository = $this->mock(ReviewRepositoryInterface::class);
    $this->geoMetricService = new GeoMetricService($this->reviewRepository);
    Cache::flush();
});

it('calculates metrics for a user', function (): void {
    $user = User::factory()->withBaseRoles()->create();
    $reviews = new Collection([
        Review::factory()->make(['rating' => 5, 'sentiment' => 'positive', 'source' => 'google', 'published_at' => now()->subDays(2)]),
        Review::factory()->make(['rating' => 4, 'sentiment' => 'positive', 'source' => 'google', 'published_at' => now()->subDays(1)]),
        Review::factory()->make(['rating' => 3, 'sentiment' => 'negative', 'source' => 'yelp', 'published_at' => now()]),
    ]);

    $this->reviewRepository->shouldReceive('getForUserAndLocation')
        ->once()
        ->with($user, null)
        ->andReturn($reviews);

    Cache::shouldReceive('tags')
        ->with(['reviews', 'locations'])
        ->andReturnSelf();
    Cache::shouldReceive('remember')
        ->once()
        ->with(Mockery::any(), Mockery::type(CarbonImmutable::class), Mockery::type(Closure::class))
        ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

    $metrics = $this->geoMetricService->calculateForUser($user);
    expect($metrics['average_rating'])->toBe((float) (5 + 4 + 3) / 3)
        ->and($metrics['total_reviews'])->toBe(3)
        ->and($metrics['sentiment_distribution'])->toEqual(collect(['positive' => 2, 'negative' => 1]))
        ->and($metrics['source_distribution'])->toEqual(collect(['google' => 2, 'yelp' => 1]))
        ->and($metrics['rating_dynamics'])->toHaveCount(3);
});

it('calculates metrics for a user and specific location', function (): void {
    $user = User::factory()->withBaseRoles()->create();
    $locationId = 1;
    $reviews = new Collection([
        Review::factory()->make(['rating' => 5, 'sentiment' => 'positive', 'source' => 'google', 'published_at' => now()]),
        Review::factory()->make(['rating' => 4, 'sentiment' => 'positive', 'source' => 'google', 'published_at' => now()]),
    ]);

    $this->reviewRepository->shouldReceive('getForUserAndLocation')
        ->once()
        ->with($user, $locationId)
        ->andReturn($reviews);

    Cache::shouldReceive('tags')
        ->with(['reviews', 'locations'])
        ->andReturnSelf();
    Cache::shouldReceive('remember')
        ->once()
        ->with(Mockery::any(), Mockery::type(CarbonImmutable::class), Mockery::type(Closure::class))
        ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

    $metrics = $this->geoMetricService->calculateForUser($user, $locationId);

    expect($metrics['average_rating'])->toBe((5 + 4) / 2)
        ->and($metrics['total_reviews'])->toBe(2)
        ->and($metrics['sentiment_distribution'])->toEqual(collect(['positive' => 2]))
        ->and($metrics['source_distribution'])->toEqual(collect(['google' => 2]))
        ->and($metrics['rating_dynamics'])->toHaveCount(1);
});

it('returns cached metrics if available', function (): void {
    $user = User::factory()->withBaseRoles()->create();
    $cachedMetrics = ['average_rating' => 4.5, 'total_reviews' => 10];
    $cacheKey = sprintf(CacheKeyEnum::GEO_METRICS_USER->value, $user->id).'all';

    Cache::shouldReceive('tags')
        ->with(['reviews', 'locations'])
        ->andReturnSelf();
    Cache::shouldReceive('remember')
        ->with($cacheKey, Mockery::type(CarbonImmutable::class), Mockery::type(Closure::class))
        ->andReturn($cachedMetrics);

    $this->reviewRepository->shouldNotReceive('getForUserAndLocation');

    $metrics = $this->geoMetricService->calculateForUser($user);

    expect($metrics)->toEqual($cachedMetrics);
});
