<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Product\Domain\Models\Product;
use App\Modules\User\Domain\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'sku' => fake()->unique()->bothify('PROD-###-???'),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'images' => [],
            'attributes' => [],
            'cost_price' => fake()->numberBetween(1000, 50000),
        ];
    }
}
