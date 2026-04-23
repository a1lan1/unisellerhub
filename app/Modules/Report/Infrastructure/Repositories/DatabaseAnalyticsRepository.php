<?php

declare(strict_types=1);

namespace App\Modules\Report\Infrastructure\Repositories;

use App\Modules\Report\Domain\Repositories\AnalyticsRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DatabaseAnalyticsRepository implements AnalyticsRepositoryInterface
{
    public function getProductRevenue(int $organizationId, int $days = 30): Collection
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('product_listings', 'order_items.product_listing_id', '=', 'product_listings.id')
            ->join('products', 'product_listings.product_id', '=', 'products.id')
            ->where('orders.organization_id', $organizationId)
            ->where('orders.order_date', '>=', now()->subDays($days))
            ->select(
                'product_listings.vendor_code as sku',
                'products.name as product_name',
                DB::raw('SUM(order_items.price * order_items.quantity) as revenue')
            )
            ->groupBy('product_listings.vendor_code', 'products.name')
            ->orderByDesc('revenue')
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
