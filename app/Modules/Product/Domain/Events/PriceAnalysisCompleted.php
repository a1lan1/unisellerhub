<?php

declare(strict_types=1);

namespace App\Modules\Product\Domain\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class PriceAnalysisCompleted
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @param array{
     *     avg_daily_sales: float,
     *     days_left: int,
     *     trend: string,
     *     current_stock: int
     * } $stats
     */
    public function __construct(
        public int $organizationId,
        public string $marketplace,
        public array $stats,
        public string|int|null $productId = null
    ) {}
}
