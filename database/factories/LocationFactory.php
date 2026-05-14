<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Geo\Domain\Enums\LocationTypeEnum;
use App\Modules\Geo\Domain\Models\Location;
use App\Modules\Shared\ValueObjects\Address;
use App\Modules\User\Domain\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->withBaseRoles(),
            'name' => fake()->company().fake()->randomDigit(),
            'type' => fake()->randomElement(LocationTypeEnum::cases()),
            'address' => new Address(
                country: fake()->countryCode(),
                city: fake()->city(),
                street: fake()->streetName(),
                houseNumber: fake()->buildingNumber(),
                postalCode: fake()->postcode(),
                fullAddress: fake()->address(),
            ),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'external_ids' => ['google' => fake()->uuid()],
        ];
    }
}
