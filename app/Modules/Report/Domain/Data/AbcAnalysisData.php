<?php

declare(strict_types=1);

namespace App\Modules\Report\Domain\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class AbcAnalysisData extends Data
{
    public function __construct(
        /** @var array<string, int> */
        public array $summary,
        /** @var DataCollection<int, AbcAnalysisItemData> */
        public DataCollection $items,
    ) {}

    public static function emptyAnalysis(): self
    {
        return self::from([
            'summary' => ['A' => 0, 'B' => 0, 'C' => 0],
            'items' => [],
        ]);
    }
}
