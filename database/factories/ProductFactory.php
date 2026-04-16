<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Product\Domain\Models\Product;
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
            'sku' => fake()->unique()->bothify('PROD-###-???'),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'images' => [],
            'attributes' => [],
        ];
    }
}
