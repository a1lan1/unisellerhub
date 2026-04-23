<?php

declare(strict_types=1);

namespace App\Modules\Report\Domain\Data;

use Spatie\LaravelData\Data;

class ProfitabilityItemData extends Data
{
    public function __construct(
        public int $id,
        public string $marketplace,
        public string $sku,
        public string $name,
        public float $price,
        public float $cost_price,
        public float $commission_percent,
        public float $logistic_cost,
        public float $profit,
        public float $margin,
    ) {}
}
