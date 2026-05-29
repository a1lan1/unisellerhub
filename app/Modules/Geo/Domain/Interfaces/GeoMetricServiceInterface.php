<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\Interfaces;

use App\Modules\User\Domain\Models\User;

interface GeoMetricServiceInterface
{
    /**
     * @return array<string, mixed>
     */
    public function calculateForUser(User $user, ?int $locationId = null): array;
}
