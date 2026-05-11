<?php

declare(strict_types=1);

namespace App\Modules\User\Domain\Interfaces;

use App\Modules\User\Domain\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;

interface NotificationRepositoryInterface
{
    /**
     * @return Collection<int, DatabaseNotification>
     */
    public function getForUser(User $user, int $limit = 20): Collection;

    public function getUnreadCount(User $user): int;

    public function findByIdForUser(User $user, string $id): ?DatabaseNotification;

    public function markAsRead(User $user, string $id): bool;

    public function markAllAsRead(User $user): void;

    public function delete(User $user, string $id): bool;

    public function clearAll(User $user): void;
}
