<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Warehouse>
 */
class WarehouseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->city().' Warehouse',
            'marketplace' => fake()->randomElement(MarketplaceEnum::cases()),
            'external_id' => fake()->uuid(),
            'address' => fake()->streetAddress(),
        ];
    }
}
