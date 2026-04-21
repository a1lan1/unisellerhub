<?php

declare(strict_types=1);

namespace Database\Factories\MockMarketplace;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\MockMarketplace\Domain\Models\MockProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MockProduct>
 */
class MockProductFactory extends Factory
{
    protected $model = MockProduct::class;

    public function definition(): array
    {
        $name = fake()->words(3, true);
        $price = fake()->randomFloat(2, 100, 10000);
        $oldPrice = $price * fake()->randomFloat(2, 1.05, 1.5);

        return [
            'mock_marketplace_account_id' => MockMarketplaceAccountFactory::new(),
            'marketplace' => MarketplaceEnum::WB,
            'external_id' => (string) fake()->numberBetween(1000000, 999999999),
            'vendor_code' => fake()->unique()->bothify('???-###'),
            'barcode' => fake()->ean13(),
            'name' => $name,
            'description' => fake()->paragraph(),
            'price' => $price,
            'old_price' => $oldPrice,
            'discount' => fake()->randomElement([0, 5, 10, 15, 20, 25]),
            'brand' => fake()->company(),
            'category' => fake()->randomElement(['Electronics', 'Apparel', 'Home Goods', 'Books']),
            'images' => [
                fake()->imageUrl(640, 480, 'products', true, $name),
                fake()->imageUrl(640, 480, 'products', true, $name.' 2'),
            ],
            'attributes' => [
                'color' => fake()->colorName(),
                'size' => fake()->randomElement(['S', 'M', 'L', 'XL']),
            ],
            'width' => fake()->randomFloat(2, 10, 50),
            'height' => fake()->randomFloat(2, 10, 50),
            'depth' => fake()->randomFloat(2, 10, 50),
            'weight' => fake()->randomFloat(2, 0.1, 5),
            'is_active' => fake()->boolean(90),
        ];
    }

    /**
     * State for MoySklad products (UUIDs)
     */
    public function moysklad(): static
    {
        return $this->state(fn (array $attributes): array => [
            'marketplace' => MarketplaceEnum::MOYSKLAD,
            'external_id' => fake()->uuid(),
        ]);
    }
}
