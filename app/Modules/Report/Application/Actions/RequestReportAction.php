<?php

declare(strict_types=1);

namespace App\Modules\Report\Application\Actions;

use App\Modules\Report\Domain\Data\RequestReportTaskData;
use App\Modules\Shared\Domain\Enums\QueueNameEnum;
use Illuminate\Support\Facades\Queue;

final class RequestReportAction
{
    /**
     * Request a report generation from the Python service.
     */
    public function execute(RequestReportTaskData $taskData): void
    {
        // Send to RabbitMQ report queue
        Queue::connection('rabbitmq')->pushRaw(
            $taskData->toJsonEncode(),
            QueueNameEnum::ReportTasks->value
        );
    }
}
