<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Order\Domain\Models\Order;
use App\Modules\User\Domain\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'marketplace' => fake()->randomElement(MarketplaceEnum::cases()),
            'external_id' => (string) fake()->unique()->numberBetween(100000, 999999),
            'status' => 'new',
            'total_price' => fake()->numberBetween(1000, 100000), // In kopeks
            'order_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'organization_id' => Organization::factory(),
            'delivery_info' => [
                'customer_name' => fake()->name(),
                'address' => fake()->address(),
            ],
        ];
    }
}
