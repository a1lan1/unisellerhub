<?php

declare(strict_types=1);

namespace App\Modules\Report\Domain\Data;

readonly class SalesStatsData
{
    public function __construct(
        public int $count,
        public int $totalCents,
        public string $currency = 'RUB'
    ) {}
}
