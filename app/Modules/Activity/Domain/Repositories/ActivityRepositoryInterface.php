<?php

declare(strict_types=1);

namespace App\Modules\Activity\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

interface ActivityRepositoryInterface
{
    /**
     * Get the latest activities for a given organization.
     *
     * @return Collection<int, Activity>
     */
    public function getLatestActivitiesForOrganization(int $organizationId, int $limit = 15): Collection;

    /**
     * Get paginated activities for a given organization and marketplace.
     *
     * @return LengthAwarePaginator<int, Activity>
     */
    public function getActivitiesForOrganizationByMarketplace(int $organizationId, string $marketplaceValue, int $perPage = 20): LengthAwarePaginator;
}
