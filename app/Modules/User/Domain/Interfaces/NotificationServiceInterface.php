<?php

declare(strict_types=1);

namespace App\Modules\User\Domain\Interfaces;

use App\Modules\User\Domain\Models\Organization;
use App\Modules\User\Domain\Models\User;

interface NotificationServiceInterface
{
    public function sendToUser(User $user, string $message): void;

    public function sendToOrganization(Organization $organization, string $message): void;
}
