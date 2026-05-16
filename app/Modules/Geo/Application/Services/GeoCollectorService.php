<?php

declare(strict_types=1);

namespace App\Modules\Geo\Application\Services;

use App\Modules\Geo\Domain\Enums\SentimentEnum;
use App\Modules\Geo\Domain\Interfaces\GeoCollectorServiceInterface;
use App\Modules\Geo\Domain\Models\Review;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoCollectorService implements GeoCollectorServiceInterface
{
    public function __construct(protected string $baseUrl, protected int $timeout) {}

    public function sendReviewForAnalysis(Review $review): ?SentimentEnum
    {
        if (blank($this->baseUrl)) {
            Log::warning('Geo Collector service URL is not configured.');

            return null;
        }

        $payload = [
            [
                'location_id' => $review->location_id,
                'source' => $review->source->value,
                'external_id' => $review->external_id,
                'author_name' => $review->author_name,
                'text' => $review->text,
                'rating' => $review->rating,
                'published_at' => $review->created_at->toIso8601String(),
            ],
        ];

        try {
            $response = Http::baseUrl($this->baseUrl)
                ->timeout($this->timeout)
                ->post('collect_reviews', $payload);

            if (! $response->successful()) {
                Log::error('Geo Collector service request failed.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'review_id' => $review->id,
                ]);

                return null;
            }

            $responseData = $response->json();

            if (isset($responseData[0]['sentiment'])) {
                return SentimentEnum::tryFrom($responseData[0]['sentiment']);
            }

            return null;
        } catch (Exception $exception) {
            Log::error('Could not connect to Geo Collector service.', [
                'exception' => $exception->getMessage(),
                'review_id' => $review->id,
            ]);

            return null;
        }
    }
}
