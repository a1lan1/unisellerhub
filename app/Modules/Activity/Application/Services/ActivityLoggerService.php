<?php

declare(strict_types=1);

namespace App\Modules\Activity\Application\Services;

use App\Modules\Activity\Domain\Events\ActivityLogged;
use App\Modules\Activity\Domain\Interfaces\ActivityLoggerInterface;
use Spatie\Activitylog\Models\Activity;

class ActivityLoggerService implements ActivityLoggerInterface
{
    /**
     * Log a business activity for an organization.
     */
    public function log(
        int $organizationId,
        string $message,
        string $type = 'info',
        ?array $properties = []
    ): void {
        /** @var Activity $activity */
        $activity = activity()
            ->useLog((string) $organizationId) // Store organization ID in log_name for easy filtering
            ->withProperties(array_merge($properties ?? [], ['type' => $type]))
            ->log($message);

        event(new ActivityLogged($organizationId, [
            'id' => $activity->id,
            'description' => $activity->description,
            'properties' => $activity->properties,
            'created_at' => $activity->created_at->toDateTimeString(),
            'human_time' => $activity->created_at->diffForHumans(),
        ]));
    }
}
