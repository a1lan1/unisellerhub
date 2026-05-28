<?php

declare(strict_types=1);

namespace App\Modules\PriceAnalysis\Domain\Data;

use App\Modules\Inventory\Domain\ValueObjects\Quantity;
use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\PriceAnalysis\Domain\ValueObjects\SalesHistoryItem;
use App\Modules\Product\ValueObjects\Sku;
use App\Modules\Report\Domain\ValueObjects\BatchId;
use App\Modules\Report\Domain\ValueObjects\ReportDisplayName;
use Illuminate\Support\Collection;
use Override;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class PriceAnalysisTaskData extends Data
{
    public function __construct(
        public int $organization_id,
        public Sku $sku,
        public Quantity $current_stock,
        /**
         * @var Collection<int, SalesHistoryItem>
         */
        public Collection $sales_history,
        public ?MarketplaceEnum $marketplace = null,
        public ?int $product_id = null,
        public ?BatchId $batch_id = null,
        public ?string $id = null,
        public ?ReportDisplayName $displayName = null,
    ) {}

    #[Override]
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'displayName' => $this->displayName?->getValue(),
            'organization_id' => $this->organization_id,
            'sku' => $this->sku->getValue(),
            'current_stock' => $this->current_stock->getValue(),
            'sales_history' => $this->sales_history->map(fn ($item): array => $item->toArray()),
            'marketplace' => $this->marketplace->value,
            'product_id' => $this->product_id,
            'batch_id' => $this->batch_id?->getValue(),
        ];
    }
}
