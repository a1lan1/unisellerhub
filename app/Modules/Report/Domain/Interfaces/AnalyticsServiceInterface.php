<?php

declare(strict_types=1);

namespace App\Modules\Report\Domain\Interfaces;

use App\Modules\Report\Domain\Data\AbcAnalysisData;
use App\Modules\Report\Domain\Data\ProfitabilityItemData;
use App\Modules\User\Domain\Models\User;

interface AnalyticsServiceInterface
{
    /**
     * Perform ABC Analysis based on revenue for a given period.
     */
    public function getAbcAnalysis(User $user, string $endDate, int $days): AbcAnalysisData;

    /**
     * Get Profitability Analysis for all products.
     *
     * @return array<int, ProfitabilityItemData>
     */
    public function getProfitabilityAnalysis(User $user): array;
}
