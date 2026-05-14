<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\Enums;

enum SentimentEnum: string
{
    case Positive = 'positive';
    case Neutral = 'neutral';
    case Negative = 'negative';
    case Unknown = 'unknown';
}
