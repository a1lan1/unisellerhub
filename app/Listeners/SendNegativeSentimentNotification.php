<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Modules\Geo\Domain\Events\NegativeSentimentDetected;
use App\Modules\Shared\Domain\ValueObjects\Url;
use App\Modules\User\Application\Services\NotificationService;
use App\Modules\User\Domain\Data\NotificationData;
use App\Modules\User\Domain\Enums\NotificationTypeEnum;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendNegativeSentimentNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(private readonly NotificationService $notificationService) {}

    public function handle(NegativeSentimentDetected $event): void
    {
        $review = $event->review;
        $recipient = $review->location->seller;

        $notificationData = new NotificationData(
            title: 'Negative Review Detected!',
            message: sprintf("A negative review has been detected for your location '%s'. Review: '%s'", $review->location->name, $review->text),
            type: NotificationTypeEnum::WARNING,
            actionUrl: new Url(route('geo.dashboard')),
            icon: 'mdi-alert-circle',
            channels: ['database', 'broadcast', 'mail']
        );

        $this->notificationService->sendToUser($recipient, $notificationData);
    }
}
