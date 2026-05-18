<?php

declare(strict_types=1);

namespace App\Modules\Report\Application\Services;

use App\Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use App\Modules\Report\Domain\Data\AbcAnalysisData;
use App\Modules\Report\Domain\Data\AbcAnalysisItemData;
use App\Modules\Report\Domain\Data\ProfitabilityItemData;
use App\Modules\Report\Domain\Repositories\AnalyticsRepositoryInterface;
use App\Modules\User\Domain\Models\User;

class AnalyticsService
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

            $group = $cumulativePercentage <= 80 ? 'A' : ($cumulativePercentage <= 95 ? 'B' : 'C');

            return AbcAnalysisItemData::from([
                'sku' => (string) $item->sku,
                'name' => (string) $item->product_name,
                'revenue' => (float) $item->revenue,
                'share' => round(((float) $item->revenue / $totalRevenue) * 100, 2),
                'group' => $group,
            ]);
        });

        return AbcAnalysisData::from([
            'summary' => [
                'A' => $items->where('group', 'A')->count(),
                'B' => $items->where('group', 'B')->count(),
                'C' => $items->where('group', 'C')->count(),
            ],
            'items' => $items,
        ]);
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
            $commission = $price * ((float) $item->commission_percent / 100);
            $profit = $price - $commission - (float) $item->logistic_cost - (float) $item->cost_price;
            $margin = $price > 0 ? ($profit / $price) * 100 : 0;

            return ProfitabilityItemData::from([
                'id' => (int) $item->id,
                'marketplace' => (string) $item->marketplace,
                'sku' => (string) $item->sku,
                'name' => (string) $item->name,
                'price' => $price,
                'cost_price' => (float) $item->cost_price,
                'commission_percent' => (float) $item->commission_percent,
                'logistic_cost' => (float) $item->logistic_cost,
                'profit' => round($profit, 2),
                'margin' => round($margin, 2),
            ]);
        })->all();
    }
}
