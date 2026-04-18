<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Domain\Data;

use App\Modules\Activity\Interfaces\Http\Resources\ActivityResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MarketplaceConnectionStatsData
{
    /**
     * @param  AnonymousResourceCollection<int, ActivityResource>  $recentActivity
     */
    public function __construct(
        public int $totalProducts,
        public int $totalOrders,
        public AnonymousResourceCollection $recentActivity,
    ) {}
}
