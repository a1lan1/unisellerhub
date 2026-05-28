<?php

declare(strict_types=1);

namespace Database\Factories\MockMarketplace;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\MockMarketplace\Domain\Models\MockStock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MockStock>
 */
class MockStockFactory extends Factory
{
    protected $model = MockStock::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(0, 500);

        return [
            'mock_marketplace_account_id' => MockMarketplaceAccountFactory::new(),
            'marketplace' => fake()->randomElement(MarketplaceEnum::cases()),
            'external_product_id' => fake()->uuid(),
            'sku' => fake()->bothify('SKU-###-???'),
            'external_warehouse_id' => fake()->uuid(),
            'quantity' => $quantity,
            'reserved' => fake()->numberBetween(0, $quantity),
        ];
    }
}
