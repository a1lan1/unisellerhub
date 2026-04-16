<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Inventory\Domain\Models\Inventory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inventory>
 */
class InventoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(0, 500);

        return [
            'quantity' => $quantity,
            'reserved' => fake()->numberBetween(0, $quantity),
        ];
    }
}
