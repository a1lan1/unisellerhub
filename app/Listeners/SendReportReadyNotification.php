<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Modules\Report\Domain\Events\ReportGenerated;
use App\Modules\User\Domain\Data\NotificationData;
use App\Modules\User\Domain\Enums\NotificationTypeEnum;
use App\Modules\User\Domain\Interfaces\NotificationServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;

final readonly class SendReportReadyNotification implements ShouldQueue
{
    public function __construct(private NotificationServiceInterface $notificationService) {}

    public function handle(ReportGenerated $event): void
    {
        $this->notificationService->sendToOrganizationById(
            $event->organizationId,
            new NotificationData(
                title: 'Report Ready',
                message: sprintf('Your %s report is ready for download.', $event->reportType),
                type: NotificationTypeEnum::SUCCESS,
                actionUrl: $event->downloadUrl,
                icon: 'mdi-file-excel'
            )
        );
    }
}
