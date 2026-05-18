<?php

declare(strict_types=1);

namespace App\Modules\Report\Domain\Repositories;

use Illuminate\Support\Collection;

interface AnalyticsRepositoryInterface
{
    public function getProductRevenue(int $organizationId, string $endDate, int $days = 30): Collection;

    public function getProductListingsWithCosts(int $organizationId): Collection;
}
