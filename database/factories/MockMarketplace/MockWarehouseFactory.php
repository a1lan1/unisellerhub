<?php

declare(strict_types=1);

namespace Database\Factories\MockMarketplace;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\MockMarketplace\Domain\Models\MockWarehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MockWarehouse>
 */
class MockWarehouseFactory extends Factory
{
    protected $model = MockWarehouse::class;

    public function definition(): array
    {
        return [
            'marketplace' => fake()->randomElement(MarketplaceEnum::cases()),
            'external_id' => fake()->uuid(),
            'name' => fake()->city().' Warehouse',
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'is_active' => true,
        ];
    }
}
