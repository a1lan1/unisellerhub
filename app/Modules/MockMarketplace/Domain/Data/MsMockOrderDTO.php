<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Domain\Data;

use Spatie\LaravelData\Data;

class MsMockOrderDTO extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public string $moment,
        public int $sum,
        public array $state,
    ) {}
}
