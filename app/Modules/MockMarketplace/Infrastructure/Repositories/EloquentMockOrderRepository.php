<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Infrastructure\Repositories;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\MockMarketplace\Domain\Models\MockOrder;
use App\Modules\MockMarketplace\Domain\Repositories\MockOrderRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentMockOrderRepository implements MockOrderRepositoryInterface
{
    /**
     * @return Collection<int, MockOrder>
     */
    public function getOrders(int $accountId, MarketplaceEnum $marketplace): Collection
    {
        return MockOrder::where('mock_marketplace_account_id', $accountId)
            ->where('marketplace', $marketplace)
            ->get();
    }

    /**
     * @return Collection<int, MockOrder>
     */
    public function getMsOrders(int $accountId): Collection
    {
        return MockOrder::where('mock_marketplace_account_id', $accountId)
            ->get();
    }
}
