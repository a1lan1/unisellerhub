<?php

declare(strict_types=1);

use App\Modules\Geo\Application\Services\LocationService;
use App\Modules\Geo\Domain\Data\LocationData;
use App\Modules\Geo\Domain\Enums\LocationTypeEnum;
use App\Modules\Geo\Domain\Models\Location;
use App\Modules\Geo\Domain\Repositories\LocationRepositoryInterface;
use App\Modules\Geo\Domain\ValueObjects\Coordinates;
use App\Modules\Shared\Domain\Enums\CacheKeyEnum;
use App\Modules\Shared\Domain\ValueObjects\Address;
use App\Modules\User\Domain\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    $this->locationRepository = $this->mock(LocationRepositoryInterface::class);
    $this->locationService = new LocationService($this->locationRepository);
    Cache::flush();
});

it('gets locations for a user from cache', function (): void {
    $user = User::factory()->withBaseRoles()->create();
    $locations = new Collection([Location::factory()->make()]);
    $cacheKey = sprintf(CacheKeyEnum::LOCATIONS_FOR_USER->value, $user->id);

    Cache::shouldReceive('tags')
        ->with(['locations'])
        ->andReturnSelf();
    Cache::shouldReceive('remember')
        ->with($cacheKey, Mockery::type(CarbonImmutable::class), Mockery::type(Closure::class))
        ->andReturn($locations);

    $result = $this->locationService->getLocationsForUser($user);

    expect($result)->toEqual($locations);
});

it('gets locations for a user from repository if not in cache', function (): void {
    $user = User::factory()->withBaseRoles()->create();
    $locations = new Collection([Location::factory()->make()]);
    $cacheKey = sprintf(CacheKeyEnum::LOCATIONS_FOR_USER->value, $user->id);

    Cache::shouldReceive('tags')
        ->with(['locations'])
        ->andReturnSelf();
    Cache::shouldReceive('remember')
        ->with($cacheKey, Mockery::type(CarbonImmutable::class), Mockery::type(Closure::class))
        ->andReturnUsing(function ($key, $ttl, $callback) {
            return $callback();
        });

    $this->locationRepository->shouldReceive('getForUser')
        ->once()
        ->with($user)
        ->andReturn($locations);

    $result = $this->locationService->getLocationsForUser($user);

    expect($result)->toEqual($locations);
});

it('gets location with stats from cache', function (): void {
    $location = Location::factory()->make();
    $cacheKey = sprintf(CacheKeyEnum::LOCATIONS_WITH_STATS->value, $location->id);

    Cache::shouldReceive('tags')
        ->with(['locations'])
        ->andReturnSelf();
    Cache::shouldReceive('remember')
        ->with($cacheKey, Mockery::type(CarbonImmutable::class), Mockery::type(Closure::class))
        ->andReturn($location);

    $result = $this->locationService->getLocationWithStats($location);

    expect($result)->toEqual($location);
});

it('gets location with stats from repository if not in cache', function (): void {
    $location = Location::factory()->make();
    $cacheKey = sprintf(CacheKeyEnum::LOCATIONS_WITH_STATS->value, $location->id);

    Cache::shouldReceive('tags')
        ->with(['locations'])
        ->andReturnSelf();
    Cache::shouldReceive('remember')
        ->with($cacheKey, Mockery::type(CarbonImmutable::class), Mockery::type(Closure::class))
        ->andReturnUsing(function ($key, $ttl, $callback) {
            return $callback();
        });

    $this->locationRepository->shouldReceive('getWithStats')
        ->once()
        ->with($location)
        ->andReturn($location);

    $result = $this->locationService->getLocationWithStats($location);

    expect($result)->toEqual($location);
});

it('stores a location', function (): void {
    $user = User::factory()->withBaseRoles()->create();
    $locationData = LocationData::from([
        'name' => 'Test',
        'address' => new Address(
            fullAddress: '123 Main St',
            country: 'USA',
            city: 'Anytown',
            street: 'Main St',
            houseNumber: '123',
            postalCode: '12345'
        ),
        'coordinates' => new Coordinates(latitude: 1.0, longitude: 1.0),
        'userId' => $user->id,
        'type' => LocationTypeEnum::STORE,
    ]);
    $location = Location::factory()->make();

    $this->locationRepository->shouldReceive('store')
        ->once()
        ->with($locationData)
        ->andReturn($location);

    $result = $this->locationService->storeLocation($locationData);

    expect($result)->toEqual($location);
});

it('deletes a location', function (): void {
    $location = Location::factory()->create();

    $this->locationRepository->shouldReceive('delete')
        ->once()
        ->with($location);

    $this->locationService->deleteLocation($location);

    // No assertion needed as it's a void method, we just check if the mock was called
    expect(true)->toBeTrue();
});
