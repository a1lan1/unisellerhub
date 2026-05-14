<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\Data;

use App\Modules\Geo\Domain\Enums\ReviewSourceEnum;
use App\Modules\Geo\Domain\Enums\SentimentEnum;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class ReviewFilterData extends Data
{
    public function __construct(
        public ?int $locationId = null,
        public ?ReviewSourceEnum $source = null,
        public ?SentimentEnum $sentiment = null,
    ) {}

    public function cacheKey(): string
    {
        return implode('_', [
            'loc_'.($this->locationId ?? 'all'),
            'src_'.($this->source instanceof ReviewSourceEnum ? $this->source->value : 'all'),
            'sent_'.($this->sentiment instanceof SentimentEnum ? $this->sentiment->value : 'all'),
        ]);
    }
}
