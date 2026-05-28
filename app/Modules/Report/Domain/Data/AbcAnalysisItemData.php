<?php

declare(strict_types=1);

namespace App\Modules\Report\Domain\Data;

use App\Modules\Product\Data\Transformers\SkuTransformer;
use App\Modules\Product\ValueObjects\Sku;
use App\Modules\Report\Domain\Enums\AbcGroupEnum;
use App\Modules\Shared\Data\Transformers\PercentageTransformer;
use App\Modules\Shared\Domain\ValueObjects\Percentage;
use Cknow\Money\Money;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;

class AbcAnalysisItemData extends Data
{
    public function __construct(
        #[WithTransformer(SkuTransformer::class)]
        public Sku $sku,
        public string $name,
        public Money $revenue,
        #[WithTransformer(PercentageTransformer::class)]
        public Percentage $share,
        public AbcGroupEnum $group,
    ) {}
}
