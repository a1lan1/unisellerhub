<?php

declare(strict_types=1);

use App\Modules\Geo\Application\Services\GeoCollectorService;
use App\Modules\Geo\Domain\Enums\ReviewSourceEnum;
use App\Modules\Geo\Domain\Enums\SentimentEnum;
use App\Modules\Geo\Domain\Models\Review;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

beforeEach(function (): void {
    $this->baseUrl = 'http://test-collector.com';
    $this->timeout = 30;
    $this->geoCollectorService = new GeoCollectorService($this->baseUrl, $this->timeout);
});

it('sends a review for analysis and returns sentiment', function (): void {
    $review = Review::factory()->create([
        'source' => ReviewSourceEnum::GOOGLE,
        'external_id' => 'ext123',
        'author_name' => 'John Doe',
        'text' => 'Great product!',
        'rating' => 5,
        'published_at' => now(),
    ]);

    Http::fake([
        $this->baseUrl.'/collect_reviews' => Http::response([
            ['sentiment' => 'positive'],
        ], 200),
    ]);

    $sentiment = $this->geoCollectorService->sendReviewForAnalysis($review);

    Http::assertSent(function (Request $request) use ($review): bool {
        $payload = $request->data();

        return $request->url() === $this->baseUrl.'/collect_reviews' &&
               $payload[0]['location_id'] === $review->location_id &&
               $payload[0]['source'] === $review->source->value &&
               $payload[0]['external_id'] === $review->external_id &&
               $payload[0]['author_name'] === $review->author_name &&
               $payload[0]['text'] === $review->text &&
               $payload[0]['rating'] === $review->rating &&
               $payload[0]['published_at'] === $review->published_at->toIso8601String();
    });
    expect($sentiment)->toBe(SentimentEnum::Positive);
});

it('returns null if base URL is not configured', function (): void {
    Log::shouldReceive('warning')
        ->once()
        ->with('Geo Collector service URL is not configured.');

    Log::shouldReceive('info')
        ->once()
        ->with(
            'ReviewSaved: Recipient is not a User model.',
            Mockery::hasKey('Channel')
        );

    $service = new GeoCollectorService('', $this->timeout);
    $review = Review::factory()->create();

    $sentiment = $service->sendReviewForAnalysis($review);

    expect($sentiment)->toBeNull();
});

it('logs error and returns null on unsuccessful response', function (): void {
    $review = Review::factory()->create();

    Http::fake([
        $this->baseUrl.'/collect_reviews' => Http::response('Error', 500),
    ]);

    Log::shouldReceive('error')
        ->once()
        ->with('Geo Collector service request failed.', Mockery::subset([
            'status' => 500,
            'body' => 'Error',
            'review_id' => $review->id,
        ]));

    $sentiment = $this->geoCollectorService->sendReviewForAnalysis($review);

    expect($sentiment)->toBeNull();
});

it('logs error and returns null on exception', function (): void {
    $review = Review::factory()->create();

    Http::fake([
        $this->baseUrl.'/collect_reviews' => Http::timeout(1), // Simulate timeout
    ]);

    Log::shouldReceive('error')
        ->once()
        ->with('Could not connect to Geo Collector service.', Mockery::subset([
            'review_id' => $review->id,
        ]));

    $sentiment = $this->geoCollectorService->sendReviewForAnalysis($review);

    expect($sentiment)->toBeNull();
});

it('returns null if sentiment is not present in response', function (): void {
    $review = Review::factory()->create();

    Http::fake([
        $this->baseUrl.'/collect_reviews' => Http::response([
            ['some_other_field' => 'value'],
        ], 200),
    ]);

    $sentiment = $this->geoCollectorService->sendReviewForAnalysis($review);

    expect($sentiment)->toBeNull();
});
