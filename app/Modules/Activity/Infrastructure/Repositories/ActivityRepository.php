<?php

declare(strict_types=1);

namespace App\Modules\Activity\Infrastructure\Repositories;

use App\Modules\Activity\Domain\Repositories\ActivityRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

class ActivityRepository implements ActivityRepositoryInterface
{
    /**
     * Get the latest activities for a given organization.
     *
     * @return Collection<int, Activity>
     */
    public function getLatestActivitiesForOrganization(int $organizationId, int $limit = 15): Collection
    {
        return Activity::query()
            ->where('log_name', (string) $organizationId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get paginated activities for a given organization and marketplace.
     *
     * @return LengthAwarePaginator<int, Activity>
     */
    public function getActivitiesForOrganizationByMarketplace(int $organizationId, string $marketplaceValue, int $perPage = 20): LengthAwarePaginator
    {
        return Activity::query()
            ->where('log_name', (string) $organizationId)
            ->where('properties->marketplace', $marketplaceValue)
            ->latest()
            ->paginate($perPage);
    }
}
