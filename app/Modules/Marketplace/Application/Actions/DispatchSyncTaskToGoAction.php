<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Application\Actions;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use Illuminate\Support\Facades\Queue;

final class DispatchSyncTaskToGoAction
{
    /**
     * Dispatch a marketplace sync task to the Go microservice via RabbitMQ.
     */
    public function execute(int $organizationId, MarketplaceEnum $marketplace, string $operation, array $payload = []): void
    {
        $task = [
            'organization_id' => $organizationId,
            'marketplace' => $marketplace->value,
            'operation' => $operation,
            'payload' => $payload,
        ];

        // Send raw JSON to the 'sync.tasks' queue used by the Go service
        Queue::connection('rabbitmq')->pushRaw(json_encode($task), 'sync.tasks');
    }
}
