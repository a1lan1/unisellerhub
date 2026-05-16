<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\Observers;

use App\Modules\Geo\Domain\Enums\SentimentEnum;
use App\Modules\Geo\Domain\Events\NegativeSentimentDetected;
use App\Modules\Geo\Domain\Events\ReviewSaved;
use App\Modules\Geo\Domain\Models\Review;
use App\Modules\Geo\Infrastructure\Jobs\AnalyzeReviewSentimentJob;
use Illuminate\Support\Facades\Cache;

class ReviewObserver
{
    public function saved(Review $review): void
    {
        if ($review->sentiment === null) {
            dispatch(new AnalyzeReviewSentimentJob($review));
        }

        if ($review->sentiment === SentimentEnum::Negative) {
            event(new NegativeSentimentDetected(
                $review->loadMissing('location.seller')
            ));
        }

        event(new ReviewSaved($review));

        $this->clearCache();
    }

    public function deleted(Review $review): void
    {
        $this->clearCache();
    }

    private function clearCache(): void
    {
        Cache::tags(['reviews'])->flush();
    }
}
