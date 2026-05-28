<?php

declare(strict_types=1);

namespace App\Modules\Order\Domain\Models\Builders;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Order\Domain\Data\OrderFilterData;
use App\Modules\Order\Domain\Models\Order;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModelClass of Order
 *
 * @extends Builder<TModelClass>
 */
class OrderBuilder extends Builder
{
    public function forOrganization(int $organizationId): self
    {
        return $this->where('organization_id', $organizationId);
    }

    public function forMarketplace(MarketplaceEnum $marketplace): self
    {
        return $this->where('marketplace', $marketplace);
    }

    public function filter(OrderFilterData $filter): self
    {
        return $this->when($filter->marketplace, fn (Builder $q, $m) => $q->where('marketplace', $m))
            ->when($filter->statuses, fn (Builder $q, array $s) => $q->whereIn('status', $s))
            ->when($filter->dateRange, fn (Builder $q, $dr) => $q->whereDate('order_date', '>=', $dr->from)->whereDate('order_date', '<=', $dr->to))
            ->when($filter->search, function (Builder $q, string $s): void {
                $q->where('external_id', 'like', sprintf('%%%s%%', $s));
            })
            ->when($filter->sortOrder, function (Builder $q, $sortOrder): void {
                $q->orderBy($sortOrder->getField(), $sortOrder->getDirection());
            }, fn (Builder $q) => $q->latest('order_date'));
    }

    public function forDate(string $date): self
    {
        return $this->whereDate('order_date', $date);
    }
}
