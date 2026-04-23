<?php

declare(strict_types=1);

namespace App\Modules\Report\Domain\Data;

use Spatie\LaravelData\Data;

class AbcAnalysisItemData extends Data
{
    public function __construct(
        public string $sku,
        public string $name,
        public float $revenue,
        public float $share,
        public string $group,
    ) {}
}
