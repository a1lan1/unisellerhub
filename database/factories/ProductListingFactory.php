<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
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
        return [
            'marketplace' => fake()->randomElement(MarketplaceEnum::cases()),
            'external_id' => fake()->uuid(),
            'vendor_code' => fake()->bothify('???-###'),
            'price' => fake()->randomFloat(2, 100, 10000),
            'status' => 'active',
            'last_synced_at' => null,
        ];
    }
}
