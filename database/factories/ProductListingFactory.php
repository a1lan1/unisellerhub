<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Product\Domain\Models\Product;
use App\Modules\Product\Domain\Models\ProductListing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductListing>
 */
class ProductListingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = fake()->numberBetween(10000, 100000);
        $oldPrice = fake()->boolean(30) ? fake()->numberBetween($price, $price * 1.5) : null;

        return [
            'product_id' => Product::factory(),
            'marketplace' => fake()->randomElement(MarketplaceEnum::cases()),
            'external_id' => fake()->uuid(),
            'vendor_code' => fake()->bothify('???-###'),
            'price' => $price,
            'old_price' => $oldPrice,
            'discount' => $oldPrice ? fake()->numberBetween(5, 50) : 0,
            'commission_percent' => fake()->randomFloat(2, 1, 20),
            'logistic_cost' => fake()->numberBetween(100, 1000),
            'status' => 'active',
            'last_synced_at' => null,
        ];
    }
}
