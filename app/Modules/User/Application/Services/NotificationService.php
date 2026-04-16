<?php

declare(strict_types=1);

namespace App\Modules\User\Application\Services;

use App\Modules\User\Domain\Interfaces\NotificationServiceInterface;
use App\Modules\User\Domain\Models\Organization;
use App\Modules\User\Domain\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Notification;

class NotificationService implements NotificationServiceInterface
{
    /**
     * Send notification to a specific user.
     */
    public function sendToUser(User $user, string $message): void
    {
        $user->notify(new SystemNotification(
            title: 'Notification',
            message: $message,
            type: 'info'
        ));
    }

    /**
     * Send notification to all users in an organization.
     */
    public function sendToOrganization(Organization $organization, string $message): void
    {
        $organization->loadMissing('users');

        if ($organization->users->isNotEmpty()) {
            Notification::send($organization->users, new SystemNotification(
                title: 'Organization Alert',
                message: $message,
                type: 'info'
            ));
        }
    }
}
