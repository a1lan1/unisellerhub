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
            'mock_marketplace_account_id' => MockMarketplaceAccountFactory::new(),
            'marketplace' => fake()->randomElement(MarketplaceEnum::cases()),
            'external_id' => (string) fake()->numberBetween(1000, 999999),
            'name' => fake()->city().' Warehouse',
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'is_active' => true,
        ];
    }
}
