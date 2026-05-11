<?php

declare(strict_types=1);

namespace App\Modules\PriceAnalysis\Domain\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class PriceAnalysisTaskData extends Data
{
    public function __construct(
        public int $organization_id,
        public string $sku,
        public int $current_stock,
        /**
         * @var array<array-key, array{date: string, quantity: int}>
         */
        public array $sales_history,
        public ?string $marketplace = null,
        public ?int $product_id = null,
        public ?string $batch_id = null,
        public ?string $id = null,
        public ?string $displayName = null,
    ) {}
}
