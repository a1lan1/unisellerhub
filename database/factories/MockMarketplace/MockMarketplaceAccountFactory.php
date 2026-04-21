<?php

declare(strict_types=1);

namespace Database\Factories\MockMarketplace;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\MockMarketplace\Domain\Models\MockMarketplaceAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MockMarketplaceAccount>
 */
class MockMarketplaceAccountFactory extends Factory
{
    protected $model = MockMarketplaceAccount::class;

    public function definition(): array
    {
        return [
            'marketplace' => fake()->randomElement(MarketplaceEnum::cases()),
            'name' => fake()->company().' Mock Store',
        ];
    }
}
