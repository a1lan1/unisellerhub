<?php

declare(strict_types=1);

namespace App\Modules\Report\Domain\Data;

use App\Modules\Report\Domain\Enums\AbcGroupEnum;
use App\Modules\Report\Domain\ValueObjects\AbcSummary;
use Override;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class AbcAnalysisData extends Data
{
    public function __construct(
        public AbcSummary $summary,
        /** @var DataCollection<int, AbcAnalysisItemData> */
        public DataCollection $items,
    ) {}

    public static function emptyAnalysis(): self
    {
        return new self(
            summary: new AbcSummary([
                AbcGroupEnum::A->value => 0,
                AbcGroupEnum::B->value => 0,
                AbcGroupEnum::C->value => 0,
            ]),
            items: new DataCollection(AbcAnalysisItemData::class, []),
        );
    }

    #[Override]
    public function toArray(): array
    {
        return [
            'summary' => $this->summary->getCounts(),
            'items' => $this->items->toArray(),
        ];
    }
}
