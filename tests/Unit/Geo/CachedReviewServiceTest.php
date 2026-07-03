<?php

declare(strict_types=1);

use App\Modules\Geo\Application\Services\CachedReviewService;
use App\Modules\Geo\Domain\Data\ReviewFilterData;
use App\Modules\Geo\Domain\Interfaces\ReviewServiceInterface;
use App\Modules\Shared\Domain\Enums\CacheKeyEnum;
use App\Modules\User\Domain\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    $this->baseReviewService = $this->mock(ReviewServiceInterface::class);
    $this->cachedReviewService = new CachedReviewService($this->baseReviewService);
    Cache::flush();
});

it('gets reviews for a user from cache', function (): void {
    $user = User::factory()->withBaseRoles()->create();
    $filters = ReviewFilterData::from(['rating' => 5]);
    $page = 1;
    $paginator = $this->mock(LengthAwarePaginator::class);
    $cacheKey = sprintf(CacheKeyEnum::REVIEWS_USER->value, $user->id, $filters->cacheKey(), $page);

    Cache::shouldReceive('tags')
        ->with(['reviews'])
        ->andReturnSelf();
    Cache::shouldReceive('remember')
        ->with($cacheKey, Mockery::type(CarbonImmutable::class), Mockery::type(Closure::class))
        ->andReturn($paginator);

    $this->baseReviewService->shouldNotReceive('getReviewsForUser');

    $result = $this->cachedReviewService->getReviewsForUser($user, $filters, $page);

    expect($result)->toEqual($paginator);
});

it('gets reviews for a user from base service if not in cache', function (): void {
    $user = User::factory()->withBaseRoles()->create();
    $filters = ReviewFilterData::from(['rating' => 5]);
    $page = 1;
    $paginator = $this->mock(LengthAwarePaginator::class);
    $cacheKey = sprintf(CacheKeyEnum::REVIEWS_USER->value, $user->id, $filters->cacheKey(), $page);

    Cache::shouldReceive('tags')
        ->with(['reviews'])
        ->andReturnSelf();
    Cache::shouldReceive('remember')
        ->with($cacheKey, Mockery::type(CarbonImmutable::class), Mockery::type(Closure::class))
        ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

    $this->baseReviewService->shouldReceive('getReviewsForUser')
        ->once()
        ->with($user, $filters, $page)
        ->andReturn($paginator);

    $result = $this->cachedReviewService->getReviewsForUser($user, $filters, $page);

    expect($result)->toEqual($paginator);
});
