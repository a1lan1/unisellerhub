<?php

declare(strict_types=1);

namespace App\Modules\Report\Domain\Data;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Product\Data\Transformers\SkuTransformer;
use App\Modules\Product\ValueObjects\Sku;
use App\Modules\Shared\Data\Transformers\PercentageTransformer;
use App\Modules\Shared\Domain\ValueObjects\Percentage;
use Cknow\Money\Money;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class ProfitabilityItemData extends Data
{
    public function __construct(
        public int $id,
        public MarketplaceEnum $marketplace,
        #[WithTransformer(SkuTransformer::class)]
        public Sku $sku,
        public string $name,
        public Money $price,
        public Money $costPrice,
        #[WithTransformer(PercentageTransformer::class)]
        public Percentage $commissionPercent,
        public Money $logisticCost,
        public Money $profit,
        #[WithTransformer(PercentageTransformer::class)]
        public Percentage $margin,
    ) {}
}
