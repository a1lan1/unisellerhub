<?php

declare(strict_types=1);

namespace App\Modules\Order\Infrastructure\Repositories;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Order\Domain\Data\OrderFilterData;
use App\Modules\Order\Domain\Enums\OrderStatusEnum;
use App\Modules\Order\Domain\Models\Order;
use App\Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use App\Modules\Report\Domain\Data\SalesStatsData;
use Carbon\CarbonInterface;
use Flowframe\Trend\Trend;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function getOrdersCountForDashboard(string $date): int
    {
        return Order::query()
            ->forDate($date)
            ->count();
    }

    public function getSalesAmountForDashboard(string $date): float
    {
        return (float) Order::query()
            ->forDate($date)
            ->sum('total_price');
    }

    public function getSalesTrend(CarbonInterface $startDate, CarbonInterface $endDate): Collection
    {
        return Trend::model(Order::class)
            ->dateColumn('order_date')
            ->between(
                start: $startDate,
                end: $endDate,
            )
            ->perDay()
            ->sum('total_price')
            ->map(fn ($item): array => [
                'date' => $item->date,
                'aggregate' => $item->aggregate,
            ]);
    }

    public function getMarketplaceDistribution(): Collection
    {
        return Order::query()
            ->selectRaw('marketplace, count(*) as count')
            ->groupBy('marketplace')
            ->get();
    }

    public function findByExternalId(MarketplaceEnum $marketplace, string $externalId): ?Order
    {
        return Order::query()
            ->forMarketplace($marketplace)
            ->where('external_id', $externalId)
            ->first();
    }

    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function update(Order $order, array $data): Order
    {
        $order->update($data);

        return $order;
    }

    public function getPaginatedOrders(OrderFilterData $filter): LengthAwarePaginator
    {
        return Order::query()
            ->filter($filter)
            ->with('items.listing.product')
            ->paginate($filter->per_page, ['*'], 'page', $filter->page);
    }

    public function sumTotalAmountByStatus(OrderStatusEnum $status): int
    {
        return (int) Order::query()
            ->where('status', $status)
            ->sum('total_amount');
    }

    public function getSalesStatsByCurrency(): ?SalesStatsData
    {
        $stats = Order::query()
            ->selectRaw('count(*) as count, sum(total_amount) as total')
            ->toBase()
            ->first();

        if (! $stats || ! $stats->count) {
            return null;
        }

        return new SalesStatsData(
            count: (int) $stats->count,
            totalCents: (int) $stats->total
        );
    }
}
