<?php

declare(strict_types=1);

namespace App\Modules\Order\Domain\Repositories;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Order\Domain\Data\OrderFilterData;
use App\Modules\Order\Domain\Enums\OrderStatusEnum;
use App\Modules\Order\Domain\Models\Order;
use App\Modules\Report\Domain\Data\SalesStatsData;
use Carbon\CarbonInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface OrderRepositoryInterface
{
    public function getOrdersCountForDashboard(string $date): int;

    public function getSalesAmountForDashboard(string $date): float;

    public function getSalesTrend(CarbonInterface $startDate, CarbonInterface $endDate): Collection;

    public function getMarketplaceDistribution(): Collection;

    public function findByExternalId(MarketplaceEnum $marketplace, string $externalId): ?Order;

    public function create(array $data): Order;

    public function update(Order $order, array $data): Order;

    public function getPaginatedOrders(OrderFilterData $filter): LengthAwarePaginator;

    public function sumTotalAmountByStatus(OrderStatusEnum $status): int;

    public function getSalesStatsByCurrency(): ?SalesStatsData;
}
