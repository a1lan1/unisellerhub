<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\Data;

use App\Modules\Geo\Domain\Enums\ReviewSourceEnum;
use App\Modules\Geo\Domain\Enums\SentimentEnum;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class ReviewData extends Data
{
    public function __construct(
        public int $locationId,
        public ReviewSourceEnum $source,
        public string $authorName,
        public string $text,
        public int $rating,
        public string $externalId,
        public ?SentimentEnum $sentiment,
        public string $publishedAt,
    ) {}
}
