<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Geo\Domain\Enums\ReviewSourceEnum;
use App\Modules\Geo\Domain\Enums\SentimentEnum;
use App\Modules\Geo\Domain\Models\Location;
use App\Modules\Geo\Domain\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'location_id' => Location::factory(),
            'source' => fake()->randomElement(ReviewSourceEnum::cases()),
            'external_id' => fake()->unique()->uuid(),
            'author_name' => fake()->name(),
            'text' => fake()->paragraph(),
            'rating' => fake()->numberBetween(1, 5),
            'sentiment' => fake()->randomElement(SentimentEnum::cases()),
            'published_at' => fake()->dateTimeThisYear(),
        ];
    }
}
