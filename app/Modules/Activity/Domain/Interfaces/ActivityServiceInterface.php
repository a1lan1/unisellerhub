<?php

declare(strict_types=1);

namespace App\Modules\Activity\Domain\Interfaces;

use App\Modules\Activity\Interfaces\Http\Resources\ActivityResource;
use App\Modules\User\Domain\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

interface ActivityServiceInterface
{
    /**
     * Get formatted latest activities for a given user's organization.
     *
     * @return AnonymousResourceCollection<int, ActivityResource>
     */
    public function getLatestFormattedActivitiesForUser(User $user, int $limit = 15): AnonymousResourceCollection;
}
