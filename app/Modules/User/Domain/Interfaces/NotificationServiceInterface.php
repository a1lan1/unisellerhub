<?php

declare(strict_types=1);

namespace App\Modules\User\Domain\Interfaces;

use App\Modules\User\Domain\Data\NotificationData;
use App\Modules\User\Domain\Models\Organization;
use App\Modules\User\Domain\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;

interface NotificationServiceInterface
{
    public function sendToUser(User $user, NotificationData $data): void;

    public function sendToOrganization(Organization $organization, NotificationData $data): void;

    public function sendToOrganizationById(int $organizationId, NotificationData $data): void;

    /**
     * @return Collection<int, DatabaseNotification>
     */
    public function getNotificationsForUser(User $user, int $limit = 20): Collection;

    public function getUnreadCount(User $user): int;

    public function markAsRead(User $user, string $id): bool;

    public function markAllAsRead(User $user): void;

    public function delete(User $user, string $id): bool;

    public function clearAll(User $user): void;
}
