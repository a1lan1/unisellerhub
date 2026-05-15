<?php

declare(strict_types=1);

namespace App\Modules\User\Application\Notifications\Channels;

use App\Modules\Shared\Application\Actions\SendTelegramNotificationAction;
use App\Modules\Shared\Domain\Data\TelegramNotificationTaskData;
use App\Modules\User\Domain\Models\User;
use Exception;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

readonly class TelegramChannel
{
    public function __construct(private SendTelegramNotificationAction $sendTelegramAction) {}

    public function send(User $notifiable, Notification $notification): void
    {
        if (! $chatId = $notifiable->telegram_chat_id) {
            Log::warning(sprintf('Telegram notification failed: User %d has no telegram_chat_id.', $notifiable->id));

            return;
        }

        if (! method_exists($notification, 'toTelegram')) {
            Log::error('Notification '.$notification::class.' is missing toTelegram method.');

            return;
        }

        $telegramMessage = $notification->toTelegram($notifiable);

        if (! isset($telegramMessage['text'])) {
            Log::error('toTelegram method for '.$notification::class.' did not return a valid message array.');

            return;
        }

        try {
            $this->sendTelegramAction->execute(
                new TelegramNotificationTaskData(
                    chatId: (int) $chatId,
                    text: $telegramMessage['text'],
                    parseMode: $telegramMessage['parse_mode'] ?? 'HTML'
                )
            );
            Log::info(sprintf('Telegram notification sent to queue for user %d, chat_id %s.', $notifiable->id, $chatId));
        } catch (Exception $exception) {
            Log::error('Failed to publish Telegram notification to RabbitMQ: '.$exception->getMessage());
        }
    }
}
