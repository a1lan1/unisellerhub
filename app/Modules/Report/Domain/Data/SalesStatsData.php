<?php

declare(strict_types=1);

namespace App\Modules\Report\Domain\Data;

use Cknow\Money\Money;

readonly class SalesStatsData
{
    public function __construct(
        public int $count,
        public Money $totalSales,
    ) {}
}
