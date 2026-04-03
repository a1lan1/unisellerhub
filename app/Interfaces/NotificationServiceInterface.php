<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\User;

interface NotificationServiceInterface
{
    public function sendToUser(User $user, string $message): void;
}
