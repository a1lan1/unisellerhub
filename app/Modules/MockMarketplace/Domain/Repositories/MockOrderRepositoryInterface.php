<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Domain\Repositories;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\MockMarketplace\Domain\Models\MockOrder;
use Illuminate\Support\Collection;

interface MockOrderRepositoryInterface
{
    /**
     * @return Collection<int, MockOrder>
     */
    public function getOrders(int $accountId, MarketplaceEnum $marketplace): Collection;

    /**
     * @return Collection<int, MockOrder>
     */
    public function getMsOrders(int $accountId): Collection;
}
