<?php

declare(strict_types=1);

use App\Modules\Geo\Domain\Data\ReviewData;
use App\Modules\Geo\Domain\Data\ReviewFilterData;
use App\Modules\Geo\Domain\Enums\ReviewSourceEnum;
use App\Modules\Geo\Domain\Enums\SentimentEnum;
use App\Modules\Geo\Domain\Models\Location;
use App\Modules\Geo\Domain\Models\Review;
use App\Modules\Geo\Infrastructure\Repositories\ReviewRepository;
use App\Modules\User\Domain\Models\Organization;
use App\Modules\User\Domain\Models\User;

beforeEach(function (): void {
    $this->repository = new ReviewRepository;
    $this->user = User::factory()->withBaseRoles()->create();
    $this->organization = Organization::factory()->create();
    $this->user->organization_id = $this->organization->id;
    $this->user->save();
});

it('gets paginated reviews for a user with filters', function (): void {
    $location = Location::factory()->create(['user_id' => $this->user->id]);
    Review::factory()->count(5)->create(['location_id' => $location->id, 'rating' => 5, 'sentiment' => SentimentEnum::Positive]);
    Review::factory()->count(3)->create(['location_id' => $location->id, 'rating' => 3, 'sentiment' => SentimentEnum::Negative]);
    Review::factory()->count(2)->create();

    $filters = ReviewFilterData::from(['rating' => 5, 'sentiment' => SentimentEnum::Positive->value]);
    $paginator = $this->repository->getForUserWithFilters($this->user, $filters);

    expect($paginator->total())->toBe(5);
    expect($paginator->first()->rating)->toBe(5);
    expect($paginator->first()->sentiment)->toBe(SentimentEnum::Positive);
});

it('gets reviews for a user and specific location', function (): void {
    $location1 = Location::factory()->create(['user_id' => $this->user->id]);
    $location2 = Location::factory()->create(['user_id' => $this->user->id]);
    Review::factory()->count(3)->create(['location_id' => $location1->id]);
    Review::factory()->count(2)->create(['location_id' => $location2->id]);
    Review::factory()->count(1)->create();

    $reviews = $this->repository->getForUserAndLocation($this->user, $location1->id);

    expect($reviews)->toHaveCount(3);
    expect($reviews->first()->location_id)->toBe($location1->id);
});

it('gets all reviews for a user if locationId is null', function (): void {
    $location1 = Location::factory()->create(['user_id' => $this->user->id]);
    $location2 = Location::factory()->create(['user_id' => $this->user->id]);
    Review::factory()->count(3)->create(['location_id' => $location1->id]);
    Review::factory()->count(2)->create(['location_id' => $location2->id]);

    $reviews = $this->repository->getForUserAndLocation($this->user, null);

    expect($reviews)->toHaveCount(5);
});

it('updates or creates a review', function (): void {
    $reviewData = ReviewData::from([
        'external_id' => 'ext-123',
        'source' => ReviewSourceEnum::GOOGLE,
        'location_id' => Location::factory()->create(['user_id' => $this->user->id])->id,
        'author_name' => 'Test Author',
        'text' => 'Test Review',
        'rating' => 4,
        'sentiment' => SentimentEnum::Neutral,
        'published_at' => now(),
    ]);

    $review = $this->repository->updateOrCreate($reviewData);

    expect($review->external_id)->toBe('ext-123');
    expect($review->source)->toBe(ReviewSourceEnum::GOOGLE);
    expect($review->author_name)->toBe('Test Author');
    $this->assertDatabaseHas('reviews', ['external_id' => 'ext-123', 'source' => ReviewSourceEnum::GOOGLE->value]);

    // Update
    $reviewData->rating = 5;
    $updatedReview = $this->repository->updateOrCreate($reviewData);

    expect($updatedReview->id)->toBe($review->id);
    expect($updatedReview->rating)->toBe(5);
    $this->assertDatabaseHas('reviews', ['id' => $review->id, 'rating' => 5]);
});
