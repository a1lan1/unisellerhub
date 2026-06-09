<?php

declare(strict_types=1);

use App\Modules\Geo\Domain\Data\LocationData;
use App\Modules\Geo\Domain\Enums\LocationTypeEnum;
use App\Modules\Geo\Domain\Models\Location;
use App\Modules\Geo\Domain\Models\Review;
use App\Modules\Geo\Domain\ValueObjects\Coordinates;
use App\Modules\Geo\Infrastructure\Repositories\LocationRepository;
use App\Modules\Shared\Domain\ValueObjects\Address;
use App\Modules\User\Domain\Models\User;

beforeEach(function (): void {
    $this->repository = new LocationRepository;
    $this->user = User::factory()->withBaseRoles()->create();
});

it('gets locations for a user with review counts and average rating', function (): void {
    $location1 = Location::factory()->create(['user_id' => $this->user->id]);
    $location2 = Location::factory()->create(['user_id' => $this->user->id]);
    Location::factory()->create(); // Another user's location

    Review::factory()->count(3)->create(['location_id' => $location1->id, 'rating' => 4]);
    Review::factory()->count(2)->create(['location_id' => $location2->id, 'rating' => 5]);

    $locations = $this->repository->getForUser($this->user);

    expect($locations)->toHaveCount(2);

    $foundLocation1 = $locations->firstWhere('id', $location1->id);
    $foundLocation2 = $locations->firstWhere('id', $location2->id);

    expect($foundLocation1)->not->toBeNull();
    expect($foundLocation1->reviews_count)->toBe(3);
    expect($foundLocation1->reviews_avg_rating)->toBe(4.0);

    expect($foundLocation2)->not->toBeNull();
    expect($foundLocation2->reviews_count)->toBe(2);
    expect($foundLocation2->reviews_avg_rating)->toBe(5.0);
});

it('stores a new location', function (): void {
    $locationData = LocationData::from([
        'user_id' => $this->user->id,
        'name' => 'New Location',
        'type' => LocationTypeEnum::STORE,
        'address' => new Address(
            fullAddress: '123 Main St',
            country: 'USA',
            city: 'Anytown',
            street: 'Main St',
            houseNumber: '123',
            postalCode: '12345'
        ),
        'coordinates' => new Coordinates(latitude: 10.0, longitude: 20.0),
        'external_ids' => ['google' => 'abc'],
    ]);

    $location = $this->repository->store($locationData);

    expect($location->name)->toBe('New Location');
    expect($location->address->fullAddress)->toBe('123 Main St');
    expect($location->external_ids)->toBe(['google' => 'abc']);
    $this->assertDatabaseHas('locations', ['name' => 'New Location']);
});

it('updates an existing location', function (): void {
    $existingLocation = Location::factory()->create(['user_id' => $this->user->id, 'name' => 'Old Name']);
    $locationData = LocationData::from([
        'id' => $existingLocation->id,
        'user_id' => $this->user->id,
        'name' => 'Updated Name',
        'type' => LocationTypeEnum::STORE,
        'address' => new Address(
            fullAddress: 'Updated Address',
            country: 'USA',
            city: 'Anytown',
            street: 'Updated St',
            houseNumber: '456',
            postalCode: '54321'
        ),
        'coordinates' => new Coordinates(latitude: 10.0, longitude: 20.0),
        'external_ids' => ['google' => 'xyz'],
    ]);

    $location = $this->repository->store($locationData);

    expect($location->id)->toBe($existingLocation->id);
    expect($location->name)->toBe('Updated Name');
    expect($location->address->fullAddress)->toBe('Updated Address');
    expect($location->external_ids)->toBe(['google' => 'xyz']);
    $this->assertDatabaseHas('locations', ['id' => $existingLocation->id, 'name' => 'Updated Name']);
});

it('gets a location with stats', function (): void {
    $location = Location::factory()->create(['user_id' => $this->user->id]);
    Review::factory()->count(5)->create(['location_id' => $location->id, 'rating' => 3]);

    $locationWithStats = $this->repository->getWithStats($location);

    expect($locationWithStats->id)->toBe($location->id);
    expect($locationWithStats->reviews_count)->toBe(5);
    expect($locationWithStats->reviews_avg_rating)->toBe(3.0);
});

it('deletes a location', function (): void {
    $location = Location::factory()->create(['user_id' => $this->user->id]);

    $this->repository->delete($location);

    $this->assertDatabaseMissing('locations', ['id' => $location->id]);
});
