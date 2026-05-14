<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\Policies;

use App\Modules\Geo\Domain\Models\Location;
use App\Modules\User\Domain\Models\User;

class LocationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Location $location): bool
    {
        return $user->id === $location->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Location $location): bool
    {
        return $user->id === $location->user_id;
    }

    public function delete(User $user, Location $location): bool
    {
        return $user->id === $location->user_id;
    }
}
