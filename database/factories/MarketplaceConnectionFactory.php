<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use App\Modules\User\Domain\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarketplaceConnection>
 */
class MarketplaceConnectionFactory extends Factory
{
    protected $model = MarketplaceConnection::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'marketplace' => fake()->randomElement(MarketplaceEnum::cases()),
            'name' => fake()->company().' Connection',
            'credentials' => [
                'token' => fake()->uuid(),
                'client_id' => fake()->numerify('######'),
            ],
            'is_active' => true,
        ];
    }
}
