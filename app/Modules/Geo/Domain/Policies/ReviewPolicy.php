<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\Policies;

use App\Modules\User\Domain\Models\User;

class ReviewPolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }
}
