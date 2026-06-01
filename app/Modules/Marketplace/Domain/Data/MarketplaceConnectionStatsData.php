<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Domain\Data;

use App\Modules\Activity\Interfaces\Http\Resources\ActivityResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Override;
use Spatie\LaravelData\Data;

class MarketplaceConnectionStatsData extends Data
{
    /**
     * @param  AnonymousResourceCollection<int, ActivityResource>  $recentActivity
     */
    public function __construct(
        public int $totalProducts,
        public int $totalOrders,
        public AnonymousResourceCollection $recentActivity,
    ) {}

    #[Override]
    public function toArray(): array
    {
        return [
            'totalProducts' => $this->totalProducts,
            'totalOrders' => $this->totalOrders,
            'recentActivity' => $this->recentActivity->toArray(Request::capture()),
        ];
    }
}
