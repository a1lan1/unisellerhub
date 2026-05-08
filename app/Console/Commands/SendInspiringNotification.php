<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Modules\User\Domain\Data\NotificationData;
use App\Modules\User\Domain\Enums\NotificationTypeEnum;
use App\Modules\User\Domain\Interfaces\NotificationServiceInterface;
use App\Modules\User\Domain\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;

final class SendInspiringNotification extends Command
{
    /**
     * @var string
     */
    protected $signature = 'app:send-inspiring-notification';

    /**
     * @var string
     */
    protected $description = 'Send a inspiring notification to all users';

    public function handle(NotificationServiceInterface $notificationService): int
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $data = new NotificationData(
            title: trim((string) $author),
            message: trim((string) $message),
            type: NotificationTypeEnum::INFO,
            icon: 'mdi-alert-circle'
        );

        User::query()->each(fn (User $user) => $notificationService->sendToUser($user, $data));

        $this->info('Inspiring notifications sent successfully.');

        return self::SUCCESS;
    }
}
