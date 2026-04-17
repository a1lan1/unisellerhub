<?php

declare(strict_types=1);

namespace App\Modules\Activity\Application\Services;

use App\Modules\Activity\Domain\Repositories\ActivityRepositoryInterface;
use App\Modules\Activity\Interfaces\Http\Resources\ActivityResource;
use App\Modules\User\Domain\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

readonly class ActivityService
{
    public function __construct(private ActivityRepositoryInterface $activityRepository) {}

    /**
     * Get formatted latest activities for a given user's organization.
     *
     * @return AnonymousResourceCollection<int, ActivityResource>
     */
    public function getLatestFormattedActivitiesForUser(User $user, int $limit = 15): AnonymousResourceCollection
    {
        if (! $user->has_organization) {
            return ActivityResource::collection(collect([]));
        }

        $activities = $this->activityRepository->getLatestActivitiesForOrganization((int) $user->organization_id, $limit);

        return ActivityResource::collection($activities);
    }
}
