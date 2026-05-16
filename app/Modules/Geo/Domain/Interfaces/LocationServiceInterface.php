<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\Interfaces;

use App\Modules\Geo\Domain\Data\LocationData;
use App\Modules\Geo\Domain\Models\Location;
use App\Modules\User\Domain\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface LocationServiceInterface
{
    /**
     * @return Collection<int, Location>
     */
    public function getLocationsForUser(User $user): Collection;

    public function storeLocation(LocationData $data): Location;

    public function getLocationWithStats(Location $location): Location;

    public function deleteLocation(Location $location): void;
}
