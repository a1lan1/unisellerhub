<?php

declare(strict_types=1);

namespace Database\Factories\MockMarketplace;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\MockMarketplace\Domain\Models\MockOrder;
use App\Modules\MockMarketplace\Domain\Models\MockProduct;
use App\Modules\Order\Domain\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MockOrder>
 */
class MockOrderFactory extends Factory
{
    protected $model = MockOrder::class;

    public function definition(): array
    {
        // Attempt to find a MockProduct for WB, as it's the default marketplace
        $mockProduct = MockProduct::where('marketplace', MarketplaceEnum::WB)->inRandomOrder()->first();

        $productId = (string) fake()->numberBetween(1000000, 9999999);
        $sku = fake()->bothify('???-###');

        if ($mockProduct) {
            $productId = $mockProduct->external_id;
            $sku = $mockProduct->vendor_code;
        }

        return [
            'marketplace' => MarketplaceEnum::WB,
            'external_order_id' => (string) fake()->unique()->numberBetween(1000000, 999999999),
            'status' => fake()->randomElement(OrderStatusEnum::cases()),
            'total_price' => fake()->randomFloat(2, 500, 50000),
            'items' => [
                [
                    'product_id' => $productId,
                    'quantity' => fake()->numberBetween(1, 5),
                    'price' => fake()->randomFloat(2, 100, 10000),
                    'sku' => $sku,
                ],
            ],
            'delivery_info' => [
                'customer_name' => fake()->name(),
                'address' => fake()->address(),
            ],
            'order_date' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function marketplace(MarketplaceEnum $marketplace): static
    {
        return $this->state(function (array $attributes) use ($marketplace): array {
            $mockProduct = MockProduct::where('marketplace', $marketplace)
                ->when(isset($attributes['mock_marketplace_account_id']), function ($query) use ($attributes): void {
                    $query->where('mock_marketplace_account_id', $attributes['mock_marketplace_account_id']);
                })
                ->inRandomOrder()
                ->first();

            $productId = ($marketplace === MarketplaceEnum::MOYSKLAD) ? fake()->uuid() : (string) fake()->numberBetween(1000000, 9999999);
            $sku = fake()->bothify('???-###');

            if ($mockProduct) {
                $productId = $mockProduct->external_id;
                $sku = $mockProduct->vendor_code;
            }

            return [
                'marketplace' => $marketplace,
                'external_order_id' => match ($marketplace) {
                    MarketplaceEnum::OZON => fake()->unique()->numberBetween(10000000, 99999999).'-0001-'.fake()->randomDigitNotNull(),
                    MarketplaceEnum::MOYSKLAD => fake()->unique()->uuid(),
                    default => (string) fake()->unique()->numberBetween(1000000, 999999999),
                },
                'items' => [
                    [
                        'product_id' => $productId,
                        'quantity' => fake()->numberBetween(1, 5),
                        'price' => fake()->randomFloat(2, 100, 10000),
                        'sku' => $sku,
                    ],
                ],
            ];
        });
    }
}
