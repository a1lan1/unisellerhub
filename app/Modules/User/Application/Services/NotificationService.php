<?php

declare(strict_types=1);

namespace App\Modules\User\Application\Services;

use App\Modules\User\Domain\Data\NotificationData;
use App\Modules\User\Domain\Interfaces\NotificationRepositoryInterface;
use App\Modules\User\Domain\Interfaces\NotificationServiceInterface;
use App\Modules\User\Domain\Models\Organization;
use App\Modules\User\Domain\Models\User;
use App\Modules\User\Domain\Notifications\SystemNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;

readonly class NotificationService implements NotificationServiceInterface
{
    public function __construct(
        private NotificationRepositoryInterface $repository
    ) {}

    public function sendToUser(User $user, NotificationData $data): void
    {
        $user->notify(new SystemNotification($data));
    }

    public function sendToOrganization(Organization $organization, NotificationData $data): void
    {
        $organization->loadMissing('users');

        if ($organization->users->isNotEmpty()) {
            Notification::send($organization->users, new SystemNotification($data));
        }
    }

    public function sendToOrganizationById(int $organizationId, NotificationData $data): void
    {
        $organization = Organization::find($organizationId);
        if ($organization) {
            $this->sendToOrganization($organization, $data);
        }
    }

    public function getNotificationsForUser(User $user, int $limit = 20): Collection
    {
        return $this->repository->getForUser($user, $limit);
    }

    public function getUnreadCount(User $user): int
    {
        return $this->repository->getUnreadCount($user);
    }

    public function markAsRead(User $user, string $id): bool
    {
        return $this->repository->markAsRead($user, $id);
    }

    public function markAllAsRead(User $user): void
    {
        $this->repository->markAllAsRead($user);
    }

    public function delete(User $user, string $id): bool
    {
        return $this->repository->delete($user, $id);
    }
}
