<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Application\Actions;

use App\Modules\Marketplace\Domain\Data\SearchResultItemData;
use App\Modules\Order\Domain\Models\Order;
use App\Modules\Product\Domain\Models\Product;
use Illuminate\Support\Collection;

class PerformGlobalSearchAction
{
    /**
     * Perform global search across products and orders.
     *
     * @return Collection<int, SearchResultItemData>
     */
    public function execute(string $query, int $organizationId): Collection
    {
        // Search Products
        $products = Product::search($query)
            ->where('organization_id', $organizationId)
            ->take(5)
            ->get()
            ->map(fn (Product $p): SearchResultItemData => new SearchResultItemData(
                type: 'product',
                id: (int) $p->id,
                title: (string) $p->name,
                subtitle: 'SKU: '.$p->sku,
                url: '/products?search='.$p->sku,
            ));

        // Search Orders
        $orders = Order::search($query)
            ->where('organization_id', $organizationId)
            ->take(5)
            ->get()
            ->map(fn (Order $o): SearchResultItemData => new SearchResultItemData(
                type: 'order',
                id: (int) $o->id,
                title: 'Order #'.$o->external_id,
                subtitle: 'MP: '.$o->marketplace->label(),
                url: '/orders?search='.$o->external_id,
            ));

        return $products->concat($orders);
    }
}
