<?php

declare(strict_types=1);

namespace App\Modules\PriceAnalysis\Infrastructure\Repositories;

use App\Modules\PriceAnalysis\Domain\Repositories\PriceAnalysisRepositoryInterface;
use App\Modules\Product\Domain\Models\Product;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final readonly class EloquentPriceAnalysisRepository implements PriceAnalysisRepositoryInterface
{
    public function getProductById(int $productId, int $organizationId): ?Product
    {
        return Product::query()
            ->where('id', $productId)
            ->where('organization_id', $organizationId)
            ->first();
    }

    /**
     * @param  int|array<int>  $productListingIds
     * @return Collection<int, array{product_listing_id: int, date: string, quantity: int}>
     */
    public function getSalesHistoryForProductListings(int|array $productListingIds, int $days = 30): Collection
    {
        $startDate = CarbonImmutable::now()->subDays($days);

        $productListingIds = is_array($productListingIds) ? $productListingIds : [$productListingIds];

        return DB::table('order_items')
            ->whereIn('product_listing_id', $productListingIds)
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.order_date', '>=', $startDate)
            ->select(
                'order_items.product_listing_id',
                DB::raw('DATE(orders.order_date) as date'),
                DB::raw('SUM(order_items.quantity) as quantity')
            )
            ->groupBy('order_items.product_listing_id', DB::raw('DATE(orders.order_date)'))
            ->orderBy('order_items.product_listing_id')
            ->orderBy(DB::raw('DATE(orders.order_date)'))
            ->get()
            ->map(
                /**
                 * @param  \stdClass  $item
                 * @return array{product_listing_id: int, date: string, quantity: int}
                 */
                fn ($item): array => [
                    'product_listing_id' => (int) $item->product_listing_id,
                    'date' => (string) $item->date,
                    'quantity' => (int) $item->quantity,
                ]
            );
    }
}
