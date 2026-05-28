<?php

declare(strict_types=1);

namespace App\Modules\User\Domain\Notifications;

use App\Modules\Shared\Domain\ValueObjects\Url;
use App\Modules\User\Application\Notifications\Channels\TelegramChannel;
use App\Modules\User\Domain\Data\NotificationData;
use App\Modules\User\Domain\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Override;

class SystemNotification extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $user,
        public NotificationData $data
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = $this->data->channels;

        if (in_array('telegram', $channels, true) && $notifiable->telegram_chat_id) {
            $channels = array_diff($channels, ['telegram']);
            $channels[] = TelegramChannel::class;
        }

        return $channels;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->data->toArray();
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->data->toArray());
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mailMessage = (new MailMessage)
            ->subject($this->data->title)
            ->line($this->data->message);

        if ($this->data->actionUrl instanceof Url) {
            $mailMessage->action('View Details', $this->data->actionUrl->getValue());
        }

        return $mailMessage;
    }

    /**
     * Get the Telegram representation of the notification.
     *
     * @return array<string, string>
     */
    public function toTelegram(User $notifiable): array
    {
        return [
            'text' => "<b>{$this->data->title}</b>\n".$this->data->message.
                      ($this->data->actionUrl instanceof Url ? "\n\n<a href=\"{$this->data->actionUrl->getValue()}\">View Details</a>" : ''),
            'parse_mode' => 'HTML',
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    #[Override]
    public function broadcastOn(): array
    {
        return [new PrivateChannel('App.Models.User.'.$this->user->id)];
    }
}
