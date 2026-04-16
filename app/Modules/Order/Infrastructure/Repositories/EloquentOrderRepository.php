<?php

declare(strict_types=1);

namespace App\Modules\Order\Infrastructure\Repositories;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Order\Domain\Data\OrderFilterData;
use App\Modules\Order\Domain\Models\Order;
use App\Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use Carbon\CarbonInterface;
use Flowframe\Trend\Trend;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function getOrdersCountForDashboard(string $date): int
    {
        return Order::whereDate('order_date', $date)->count();
    }

    public function getSalesAmountForDashboard(string $date): float
    {
        return (float) Order::whereDate('order_date', $date)->sum('total_price');
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
        return Order::selectRaw('marketplace, count(*) as count')
            ->groupBy('marketplace')
            ->get();
    }

    public function findByExternalId(MarketplaceEnum $marketplace, string $externalId): ?Order
    {
        return Order::query()
            ->where('marketplace', $marketplace)
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

    public function getAllOrders(): Collection
    {
        return Order::query()
            ->with('items.listing.product')
            ->latest('order_date')
            ->get();
    }

    public function getPaginatedOrders(OrderFilterData $filter): LengthAwarePaginator
    {
        return Order::query()
            ->with('items.listing.product')
            ->when($filter->marketplace, fn (Builder $q, $m) => $q->where('marketplace', $m))
            ->when($filter->statuses, fn (Builder $q, array $s) => $q->whereIn('status', $s))
            ->when($filter->date_from, fn (Builder $q, $d) => $q->whereDate('order_date', '>=', $d))
            ->when($filter->date_to, fn (Builder $q, $d) => $q->whereDate('order_date', '<=', $d))
            ->when($filter->search, function (Builder $q, string $s): void {
                $q->where('external_id', 'like', sprintf('%%%s%%', $s));
            })
            ->when($filter->sort, function (Builder $q, $s) use ($filter): void {
                $q->orderBy($s, $filter->direction ?? 'desc');
            }, fn (Builder $q) => $q->latest('order_date'))
            ->paginate($filter->per_page, ['*'], 'page', $filter->page);
    }
}
