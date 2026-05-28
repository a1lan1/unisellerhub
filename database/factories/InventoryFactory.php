<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Inventory\Domain\Models\Inventory;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Product\Domain\Models\ProductListing;
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
            'product_listing_id' => ProductListing::factory(),
            'warehouse_id' => Warehouse::factory(),
            'quantity' => $quantity,
            'reserved' => fake()->numberBetween(0, $quantity),
        ];
    }
}
