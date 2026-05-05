<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Application\Actions;

use App\Modules\Shared\Domain\Data\SyncMarketplaceTaskData;
use App\Modules\Shared\Domain\Enums\QueueNameEnum;
use Illuminate\Support\Facades\Queue;

final class DispatchSyncTaskToGoAction
{
    /**
     * Dispatch a marketplace sync task to the Go microservice via RabbitMQ.
     */
    public function execute(SyncMarketplaceTaskData $taskData): void
    {
        // Send raw JSON to the 'sync.tasks' queue used by the Go service
        Queue::connection('rabbitmq')->pushRaw(
            $taskData->toJsonEncode(),
            QueueNameEnum::SyncTasks->value
        );
    }
}
