<?php

declare(strict_types=1);

namespace App\Modules\Shared\Application\Actions;

use App\Modules\Shared\Domain\Data\TelegramNotificationTaskData;
use App\Modules\Shared\Domain\Enums\QueueNameEnum;
use Illuminate\Support\Facades\Queue;
use PhpAmqpLib\Message\AMQPMessage;

final class SendTelegramNotificationAction
{
    /**
     * Send a notification to Telegram via the Go Bot service.
     */
    public function execute(TelegramNotificationTaskData $taskData): void
    {
        // Push to the queue listened by the Go Telegram Bot
        Queue::connection('rabbitmq')->pushRaw(
            $taskData->toJsonEncode(),
            QueueNameEnum::NotificationsTelegram->value,
            ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
        );
    }
}
