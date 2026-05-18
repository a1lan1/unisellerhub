<?php

declare(strict_types=1);

namespace App\Modules\Report\Infrastructure\Repositories;

use App\Modules\Order\Domain\Models\OrderItem;
use App\Modules\Report\Domain\Repositories\AnalyticsRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DatabaseAnalyticsRepository implements AnalyticsRepositoryInterface
{
    public function getProductRevenue(int $organizationId, string $endDate, int $days = 30): Collection
    {
        return OrderItem::query()
            ->revenue($organizationId, $endDate, $days)
            ->get();
    }

    public function getProductListingsWithCosts(int $organizationId): Collection
    {
        return DB::table('product_listings')
            ->join('products', 'product_listings.product_id', '=', 'products.id')
            ->where('products.organization_id', $organizationId)
            ->select(
                'product_listings.id',
                'product_listings.marketplace',
                'product_listings.vendor_code as sku',
                'products.name as name',
                'product_listings.price',
                'product_listings.commission_percent',
                'product_listings.logistic_cost',
                'products.cost_price'
            )
            ->get();
    }
}
