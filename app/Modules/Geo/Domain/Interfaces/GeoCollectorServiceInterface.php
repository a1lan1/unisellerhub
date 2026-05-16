<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\Interfaces;

use App\Modules\Geo\Domain\Enums\SentimentEnum;
use App\Modules\Geo\Domain\Models\Review;

interface GeoCollectorServiceInterface
{
    public function sendReviewForAnalysis(Review $review): ?SentimentEnum;
}
