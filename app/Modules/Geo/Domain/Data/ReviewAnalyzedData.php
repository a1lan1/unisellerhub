<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\Data;

use Spatie\LaravelData\Data;

final class ReviewAnalyzedData extends Data
{
    public function __construct(
        public string $externalId,
        public ?int $organizationId,
        public string $sentiment,
        public string $reviewText,
        public string $location,
    ) {}
}
