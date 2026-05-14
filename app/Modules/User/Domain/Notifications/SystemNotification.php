<?php

declare(strict_types=1);

namespace App\Modules\User\Domain\Notifications;

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

    public function via(object $notifiable): array
    {
        return $this->data->channels;
    }

    public function toArray(object $notifiable): array
    {
        return $this->data->toArray();
    }

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

        if ($this->data->actionUrl) {
            $mailMessage->action('View Details', $this->data->actionUrl);
        }

        return $mailMessage;
    }

    /**
     * @return array<int, Channel>
     */
    #[Override]
    public function broadcastOn(): array
    {
        return [new PrivateChannel('App.Models.User.'.$this->user->id)];
    }
}
