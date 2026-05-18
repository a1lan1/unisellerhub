<?php

declare(strict_types=1);

namespace App\Modules\Order\Domain\Models\Builders;

use App\Modules\Order\Domain\Models\OrderItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

/**
 * @template TModelClass of OrderItem
 *
 * @extends Builder<TModelClass>
 */
class OrderItemBuilder extends Builder
{
    public function revenue(int $organizationId, string $endDate, int $days = 30): self
    {
        $endCarbonDate = Date::createFromFormat('Y-m-d', $endDate)->endOfDay();
        $startCarbonDate = $endCarbonDate->copy()->subDays($days - 1)->startOfDay();

        return $this->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('product_listings', 'order_items.product_listing_id', '=', 'product_listings.id')
            ->join('products', 'product_listings.product_id', '=', 'products.id')
            ->where('orders.organization_id', $organizationId)
            ->whereBetween('orders.order_date', [$startCarbonDate, $endCarbonDate])
            ->select(
                'product_listings.vendor_code as sku',
                'products.name as product_name',
                DB::raw('SUM(order_items.price * order_items.quantity) as revenue'),
            )
            ->groupBy('product_listings.vendor_code', 'products.name')
            ->orderByDesc('revenue');
    }
}
