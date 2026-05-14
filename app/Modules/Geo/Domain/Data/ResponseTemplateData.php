<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class ResponseTemplateData extends Data
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public string $title,
        public string $body,
    ) {}
}
