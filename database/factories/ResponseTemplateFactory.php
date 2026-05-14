<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Geo\Domain\Models\ResponseTemplate;
use App\Modules\User\Domain\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResponseTemplate>
 */
class ResponseTemplateFactory extends Factory
{
    protected $model = ResponseTemplate::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->withBaseRoles(),
            'title' => fake()->sentence(3),
            'body' => fake()->paragraph(),
        ];
    }
}
