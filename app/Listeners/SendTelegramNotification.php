<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Modules\Marketplace\Domain\Events\SyncFailed;
use App\Modules\Shared\Application\Actions\SendTelegramNotificationAction;
use App\Modules\Shared\Domain\Data\TelegramNotificationTaskData;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

final readonly class SendTelegramNotification implements ShouldQueue
{
    public function __construct(private SendTelegramNotificationAction $sendTelegramAction) {}

    public function handle(SyncFailed $event): void
    {
        $chatId = config('services.telegram.admin_chat_id');

        if (! $chatId) {
            Log::warning('Telegram Admin Chat ID not set. Cannot send notification.');

            return;
        }

        $message = sprintf(
            "⚠️ <b>Sync Failed</b>\n\n".
            "<b>Org ID:</b> %d\n".
            "<b>Type:</b> %s\n".
            "<b>Error:</b> %s\n".
            '<b>Time:</b> %s',
            $event->organizationId,
            $event->type,
            $event->message,
            now()->toDateTimeString()
        );

        try {
            $this->sendTelegramAction->execute(
                new TelegramNotificationTaskData(
                    chatId: (int) $chatId,
                    text: $message,
                )
            );
        } catch (Throwable $throwable) {
            Log::error('Failed to enqueue Telegram notification: '.$throwable->getMessage());
        }
    }
}
