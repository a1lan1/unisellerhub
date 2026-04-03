<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\UserNotification;
use App\Interfaces\NotificationServiceInterface;
use App\Models\User;

class NotificationService implements NotificationServiceInterface
{
    public function sendToUser(User $user, string $message): void
    {
        broadcast(new UserNotification($user, $message));
    }
}
