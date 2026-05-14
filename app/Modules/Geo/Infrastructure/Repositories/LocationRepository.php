<?php

declare(strict_types=1);

namespace App\Modules\Geo\Infrastructure\Repositories;

use App\Modules\Geo\Domain\Data\LocationData;
use App\Modules\Geo\Domain\Models\Location;
use App\Modules\Geo\Domain\Repositories\LocationRepositoryInterface;
use App\Modules\User\Domain\Models\User;
use Illuminate\Database\Eloquent\Collection;

class LocationRepository implements LocationRepositoryInterface
{
    public function getForUser(User $user): Collection
    {
        return $user->locations()
            ->select(['id', 'user_id', 'name', 'type', 'address', 'latitude', 'longitude', 'external_ids'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->latest()
            ->get();
    }

    public function store(LocationData $data): Location
    {
        return Location::updateOrCreate(
            ['id' => $data->id],
            $data->toArray()
        );
    }

    public function getWithStats(Location $location): Location
    {
        return $location->loadCount('reviews')->loadAvg('reviews', 'rating');
    }

    public function delete(Location $location): void
    {
        $location->delete();
    }
}
