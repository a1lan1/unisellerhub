<?php

declare(strict_types=1);

namespace App\Modules\Geo\Application\Services;

use App\Modules\Geo\Domain\Data\LocationData;
use App\Modules\Geo\Domain\Interfaces\LocationServiceInterface;
use App\Modules\Geo\Domain\Models\Location;
use App\Modules\Geo\Domain\Repositories\LocationRepositoryInterface;
use App\Modules\Shared\Domain\Enums\CacheKeyEnum;
use App\Modules\User\Domain\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;

class LocationService implements LocationServiceInterface
{
    public function __construct(protected LocationRepositoryInterface $locationRepository) {}

    /**
     * @return Collection<int, Location>
     */
    public function getLocationsForUser(User $user): Collection
    {
        return Cache::tags(['locations'])->remember(
            sprintf(CacheKeyEnum::LOCATIONS_FOR_USER->value, $user->id),
            Date::now()->addHour(),
            fn (): Collection => $this->locationRepository->getForUser($user)
        );
    }

    public function getLocationWithStats(Location $location): Location
    {
        return Cache::tags(['locations'])->remember(
            sprintf(CacheKeyEnum::LOCATIONS_WITH_STATS->value, $location->id),
            Date::now()->addHour(),
            fn (): Location => $this->locationRepository->getWithStats($location)
        );
    }

    public function storeLocation(LocationData $data): Location
    {
        return $this->locationRepository->store($data);
    }

    public function deleteLocation(Location $location): void
    {
        $this->locationRepository->delete($location);
    }
}
