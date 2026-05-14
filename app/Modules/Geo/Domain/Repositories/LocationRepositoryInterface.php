<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\Repositories;

use App\Modules\Geo\Domain\Data\LocationData;
use App\Modules\Geo\Domain\Models\Location;
use App\Modules\User\Domain\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface LocationRepositoryInterface
{
    public function getForUser(User $user): Collection;

    public function store(LocationData $data): Location;

    public function getWithStats(Location $location): Location;

    public function delete(Location $location): void;
}
