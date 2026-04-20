<?php

declare(strict_types=1);

namespace App\Modules\User\Infrastructure\Repositories;

use App\Modules\User\Domain\Interfaces\NotificationRepositoryInterface;
use App\Modules\User\Domain\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;

class EloquentNotificationRepository implements NotificationRepositoryInterface
{
    public function getForUser(User $user, int $limit = 20): Collection
    {
        return $user->notifications()->limit($limit)->get();
    }

    public function getUnreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    public function findByIdForUser(User $user, string $id): ?DatabaseNotification
    {
        return $user->notifications()->find($id);
    }

    public function markAsRead(User $user, string $id): bool
    {
        $notification = $this->findByIdForUser($user, $id);

        if ($notification) {
            $notification->markAsRead();

            return true;
        }

        return false;
    }

    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }

    public function delete(User $user, string $id): bool
    {
        $notification = $this->findByIdForUser($user, $id);

        if ($notification) {
            $notification->delete();

            return true;
        }

        return false;
    }
}
