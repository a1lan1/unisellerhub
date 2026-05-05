<?php

declare(strict_types=1);

namespace App\Modules\Order\Application\Services;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Order\Domain\Data\OrderData;
use App\Modules\Order\Domain\Data\OrderFilterData;
use App\Modules\Order\Domain\Data\SyncOrdersData;
use App\Modules\Order\Domain\Models\Order;
use App\Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use App\Modules\Order\Infrastructure\Jobs\SyncOrdersJob;
use App\Modules\User\Domain\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Date;

readonly class OrderService
{
    public function __construct(private OrderRepositoryInterface $orderRepository) {}

    public function getDashboardStats(User $user, ?string $date = null): array
    {
        if (! $user->has_organization) {
            return [
                'today_orders' => 0,
                'today_sales' => 0.0,
                'trend' => collect(),
                'distribution' => collect(),
            ];
        }

        $selectedDate = $date ? Date::parse($date) : now();

        return [
            'today_orders' => $this->orderRepository->getOrdersCountForDashboard($selectedDate->toDateString()),
            'today_sales' => $this->orderRepository->getSalesAmountForDashboard($selectedDate->toDateString()),
            'trend' => $this->orderRepository->getSalesTrend($selectedDate->copy()->subDays(30), $selectedDate),
            'distribution' => $this->orderRepository->getMarketplaceDistribution(),
        ];
    }

    public function getPaginatedOrders(User $user, OrderFilterData $filter): LengthAwarePaginator
    {
        if (! $user->has_organization) {
            return new LengthAwarePaginator([], 0, $filter->per_page);
        }

        return $this->orderRepository->getPaginatedOrders($filter);
    }

    public function updateOrCreateOrder(MarketplaceEnum $marketplace, OrderData $orderData, ?int $organizationId): Order
    {
        $order = $this->orderRepository->findByExternalId($marketplace, $orderData->external_id);

        if ($order instanceof Order) {
            return $this->orderRepository->update($order, [
                'status' => $orderData->status,
                'total_price' => $orderData->total_price->getAmount(),
                'order_date' => $orderData->order_date,
                'delivery_info' => $orderData->delivery_info,
                'last_synced_at' => now(),
            ]);
        }

        return $this->orderRepository->create([
            'organization_id' => $organizationId,
            'marketplace' => $marketplace,
            'external_id' => $orderData->external_id,
            'status' => $orderData->status,
            'total_price' => $orderData->total_price->getAmount(),
            'order_date' => $orderData->order_date,
            'delivery_info' => $orderData->delivery_info,
            'last_synced_at' => now(),
        ]);
    }

    public function syncOrders(SyncOrdersData $dto): void
    {
        dispatch(new SyncOrdersJob($dto->organizationId, $dto->marketplace));
    }
}
