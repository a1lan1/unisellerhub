<?php

declare(strict_types=1);

namespace App\Modules\User\Domain\Policies;

use App\Modules\User\Domain\Models\User;

class OrganizationPolicy
{
    /**
     * Determine whether the user can interact with an organization.
     */
    public function hasOrganization(User $user): bool
    {
        return $user->has_organization;
    }
}
