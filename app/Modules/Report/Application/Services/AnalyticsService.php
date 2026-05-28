<?php

declare(strict_types=1);

namespace App\Modules\Report\Application\Services;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use App\Modules\Product\ValueObjects\Sku;
use App\Modules\Report\Domain\Data\AbcAnalysisData;
use App\Modules\Report\Domain\Data\AbcAnalysisItemData;
use App\Modules\Report\Domain\Data\ProfitabilityItemData;
use App\Modules\Report\Domain\Enums\AbcGroupEnum;
use App\Modules\Report\Domain\Interfaces\AnalyticsServiceInterface;
use App\Modules\Report\Domain\Repositories\AnalyticsRepositoryInterface;
use App\Modules\Report\Domain\ValueObjects\AbcSummary;
use App\Modules\Shared\Domain\ValueObjects\Percentage;
use App\Modules\User\Domain\Models\User;
use Cknow\Money\Money;
use Log;
use Spatie\LaravelData\DataCollection;

class AnalyticsService implements AnalyticsServiceInterface
{
    public function __construct(
        protected AnalyticsRepositoryInterface $repository,
        protected OrderRepositoryInterface $orderRepository
    ) {}

    /**
     * Perform ABC Analysis based on revenue for a given period.
     */
    public function getAbcAnalysis(User $user, string $endDate, int $days): AbcAnalysisData
    {
        if (! $user->has_organization) {
            return AbcAnalysisData::emptyAnalysis();
        }

        $productRevenue = $this->repository->getProductRevenue((int) $user->organization_id, $endDate, $days);

        if ($productRevenue->isEmpty()) {
            return AbcAnalysisData::emptyAnalysis();
        }

        $totalRevenue = (float) $productRevenue->sum('revenue');
        $runningRevenue = 0;

        $items = $productRevenue->map(function (object $item) use ($totalRevenue, &$runningRevenue): AbcAnalysisItemData {
            $runningRevenue += (float) $item->revenue;
            $cumulativePercentage = ($runningRevenue / $totalRevenue) * 100;

            $group = $cumulativePercentage <= 80 ? AbcGroupEnum::A : ($cumulativePercentage <= 95 ? AbcGroupEnum::B : AbcGroupEnum::C);

            return new AbcAnalysisItemData(
                sku: new Sku((string) $item->sku),
                name: (string) $item->product_name,
                revenue: Money::RUB((int) $item->revenue),
                share: new Percentage(round(((float) $item->revenue / $totalRevenue) * 100, 2)),
                group: $group,
            );
        });

        return new AbcAnalysisData(
            summary: new AbcSummary([
                AbcGroupEnum::A->value => $items->where('group', AbcGroupEnum::A)->count(),
                AbcGroupEnum::B->value => $items->where('group', AbcGroupEnum::B)->count(),
                AbcGroupEnum::C->value => $items->where('group', AbcGroupEnum::C)->count(),
            ]),
            items: new DataCollection(AbcAnalysisItemData::class, $items->all()),
        );
    }

    /**
     * Get Profitability Analysis for all products.
     *
     * @return array<int, ProfitabilityItemData>
     */
    public function getProfitabilityAnalysis(User $user): array
    {
        if (! $user->has_organization) {
            return [];
        }

        $listings = $this->repository->getProductListingsWithCosts((int) $user->organization_id);

        return $listings->map(function (object $item): ProfitabilityItemData {
            $price = (float) $item->price;
            $costPrice = (float) $item->cost_price;
            $commissionPercent = (float) $item->commission_percent;
            $logisticCost = (float) $item->logistic_cost;

            $commission = $price * ($commissionPercent / 100);
            $profit = $price - $commission - $logisticCost - $costPrice;
            $margin = $price > 0 ? ($profit / $price) * 100 : 0;

            return new ProfitabilityItemData(
                id: (int) $item->id,
                marketplace: MarketplaceEnum::from((string) $item->marketplace),
                sku: new Sku((string) $item->sku),
                name: (string) $item->name,
                price: Money::RUB((int) $price),
                costPrice: Money::RUB((int) $costPrice),
                commissionPercent: new Percentage($commissionPercent),
                logisticCost: Money::RUB((int) $logisticCost),
                profit: Money::RUB((int) round($profit, 2)),
                margin: new Percentage(round($margin, 2)),
            );
        })->all();
    }
}
