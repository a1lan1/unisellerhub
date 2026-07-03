<?php

declare(strict_types=1);

use App\Modules\Geo\Application\Services\SellerService;
use App\Modules\Shared\Domain\Enums\CacheKeyEnum;
use App\Modules\User\Domain\Models\User;
use App\Modules\User\Domain\Repositories\UserRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    $this->userRepository = $this->mock(UserRepositoryInterface::class);
    $this->sellerService = new SellerService($this->userRepository);
    Cache::flush();
});

it('gets sellers from cache', function (): void {
    $sellers = new Collection([User::factory()->make()]);
    $cacheKey = CacheKeyEnum::SELLERS->value;

    Cache::shouldReceive('tags')
        ->with(['sellers'])
        ->andReturnSelf();
    Cache::shouldReceive('remember')
        ->with($cacheKey, Mockery::type(CarbonImmutable::class), Mockery::type(Closure::class))
        ->andReturn($sellers);

    $result = $this->sellerService->getSellers();

    expect($result)->toEqual($sellers);
});

it('gets sellers from repository if not in cache', function (): void {
    $sellers = new Collection([User::factory()->make()]);
    $cacheKey = CacheKeyEnum::SELLERS->value;

    Cache::shouldReceive('tags')
        ->with(['sellers'])
        ->andReturnSelf();
    Cache::shouldReceive('remember')
        ->with($cacheKey, Mockery::type(CarbonImmutable::class), Mockery::type(Closure::class))
        ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

    $this->userRepository->shouldReceive('getSellers')
        ->once()
        ->andReturn($sellers);

    $result = $this->sellerService->getSellers();

    expect($result)->toEqual($sellers);
});

it('gets seller with products from cache', function (): void {
    $seller = User::factory()->make();
    $cacheKey = sprintf(CacheKeyEnum::SELLERS_WITH_PRODUCTS->value, $seller->id);

    Cache::shouldReceive('tags')
        ->with(['sellers'])
        ->andReturnSelf();
    Cache::shouldReceive('remember')
        ->with($cacheKey, Mockery::type(CarbonImmutable::class), Mockery::type(Closure::class))
        ->andReturn($seller);

    $result = $this->sellerService->getSellerWithProducts($seller);

    expect($result)->toEqual($seller);
});

it('gets seller with products from repository if not in cache', function (): void {
    $seller = User::factory()->make();
    $cacheKey = sprintf(CacheKeyEnum::SELLERS_WITH_PRODUCTS->value, $seller->id);

    Cache::shouldReceive('tags')
        ->with(['sellers'])
        ->andReturnSelf();
    Cache::shouldReceive('remember')
        ->with($cacheKey, Mockery::type(CarbonImmutable::class), Mockery::type(Closure::class))
        ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

    $this->userRepository->shouldReceive('getSellerWithProducts')
        ->once()
        ->with($seller)
        ->andReturn($seller);

    $result = $this->sellerService->getSellerWithProducts($seller);

    expect($result)->toEqual($seller);
});
