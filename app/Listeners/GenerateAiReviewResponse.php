<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Ai\Agents\ReviewResponder;
use App\Modules\Geo\Domain\Events\ReviewAnalyzed;
use App\Modules\Shared\Domain\ValueObjects\Url;
use App\Modules\User\Domain\Data\NotificationData;
use App\Modules\User\Domain\Enums\NotificationTypeEnum;
use App\Modules\User\Domain\Interfaces\NotificationServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

final readonly class GenerateAiReviewResponse implements ShouldQueue
{
    public function __construct(private NotificationServiceInterface $notificationService) {}

    public function handle(ReviewAnalyzed $event): void
    {
        try {
            $aiResponse = ReviewResponder::make()
                ->prompt(sprintf(
                    'The customer left a %s review for location %s: "%s". Generate a short, professional response.',
                    $event->data->sentiment,
                    $event->data->location,
                    $event->data->reviewText
                ));

            Log::info(sprintf('AI Response generated for review %s: ', $event->data->externalId).$aiResponse);

            // Send AI response as a notification to the organization
            if ($event->data->organizationId !== null) {
                $notificationData = new NotificationData(
                    title: 'AI Review Response Generated',
                    message: sprintf(
                        'An AI-generated response for a %s review on %s has been created: "%s"',
                        $event->data->sentiment,
                        $event->data->location,
                        $aiResponse
                    ),
                    type: NotificationTypeEnum::INFO,
                    actionUrl: new Url(route('geo.dashboard')),
                );

                $this->notificationService->sendToOrganizationById($event->data->organizationId, $notificationData);
            }
        } catch (Throwable $throwable) {
            Log::error(sprintf('Failed to generate AI response for review %s: ', $event->data->externalId).$throwable->getMessage());
        }
    }
}
