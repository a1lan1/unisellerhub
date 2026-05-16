<?php

declare(strict_types=1);

namespace App\Modules\Geo\Infrastructure\Jobs;

use App\Modules\Geo\Domain\Data\ReviewAnalyzedData;
use App\Modules\Geo\Domain\Enums\SentimentEnum;
use App\Modules\Geo\Domain\Events\ReviewAnalyzed;
use App\Modules\Geo\Domain\Interfaces\GeoCollectorServiceInterface;
use App\Modules\Geo\Domain\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AnalyzeReviewSentimentJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public Review $review) {}

    public function handle(GeoCollectorServiceInterface $geoCollectorService): void
    {
        if (empty($this->review->text)) {
            return;
        }

        $sentiment = $geoCollectorService->sendReviewForAnalysis($this->review);

        if ($sentiment instanceof SentimentEnum) {
            $this->review->sentiment = $sentiment;
            $this->review->save();
        }

        event(new ReviewAnalyzed(
            data: new ReviewAnalyzedData(
                externalId: $this->review->external_id,
                organizationId: $this->review->location->seller->organization_id,
                sentiment: $this->review->sentiment->value ?? SentimentEnum::Neutral->value,
                reviewText: $this->review->text ?? '',
                location: $this->review->location->name ?? ''
            )
        ));
    }
}
