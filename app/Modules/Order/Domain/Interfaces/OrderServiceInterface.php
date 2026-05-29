<?php

declare(strict_types=1);

namespace App\Modules\Order\Domain\Interfaces;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Order\Domain\Data\OrderData;
use App\Modules\Order\Domain\Data\OrderFilterData;
use App\Modules\Order\Domain\Data\SyncOrdersData;
use App\Modules\Order\Domain\Models\Order;
use App\Modules\User\Domain\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderServiceInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getDashboardStats(User $user, ?string $date = null): array;

    public function getPaginatedOrders(User $user, OrderFilterData $filter): LengthAwarePaginator;

    public function updateOrCreateOrder(MarketplaceEnum $marketplace, OrderData $orderData, ?int $organizationId): Order;

    public function syncOrders(SyncOrdersData $dto): void;
}
