<?php

declare(strict_types=1);

namespace App\Modules\Shared\Application\Jobs;

use App\Modules\Activity\Domain\Enums\ActivityLogTypeEnum;
use App\Modules\Activity\Domain\Interfaces\ActivityLoggerInterface;
use App\Modules\Marketplace\Application\Actions\DispatchSyncTaskToGoAction;
use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Marketplace\Domain\Enums\SyncOperationTypeEnum;
use App\Modules\Marketplace\Domain\Events\SyncFailed;
use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use App\Modules\Shared\Application\Services\TenantManager;
use App\Modules\Shared\Domain\Data\SyncMarketplaceTaskData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Spatie\Prometheus\Facades\Prometheus;
use Throwable;

abstract class AbstractMarketplaceSyncJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $organizationId, public ?MarketplaceEnum $marketplace = null) {}

    abstract protected function getOperationType(): SyncOperationTypeEnum;

    abstract protected function getOperationLabel(): string;

    /**
     * Handle the sync process by dispatching task to Go Sync Service.
     *
     * @throws Throwable
     */
    public function handle(
        DispatchSyncTaskToGoAction $dispatchAction,
        TenantManager $tenantManager,
        ActivityLoggerInterface $logger
    ): void {
        $tenantManager->setOrganizationId($this->organizationId);

        $operationType = $this->getOperationType();
        $operationLabel = $this->getOperationLabel();

        $logger->log(
            $this->organizationId,
            sprintf('Started %s synchronization dispatch to Go Service', $operationLabel),
            ActivityLogTypeEnum::SyncStart->value
        );

        $connections = MarketplaceConnection::where('organization_id', $this->organizationId)
            ->where('is_active', true)
            ->when($this->marketplace, fn ($q) => $q->where('marketplace', $this->marketplace))
            ->get();

        foreach ($connections as $connection) {
            $startTime = microtime(true);
            $marketplaceValue = $connection->marketplace->value;

            try {
                // Dispatch to Go Sync Service via RabbitMQ with credentials
                $dispatchAction->execute(
                    new SyncMarketplaceTaskData(
                        organizationId: $this->organizationId,
                        marketplace: $connection->marketplace,
                        operation: $operationType,
                        payload: [
                            'connection_id' => $connection->id,
                            ...$connection->credentials,
                        ]
                    )
                );

                $duration = microtime(true) - $startTime;

                $logger->log(
                    $this->organizationId,
                    sprintf('%s sync task dispatched for %s', $operationLabel, $connection->marketplace->label()),
                    ActivityLogTypeEnum::SyncDispatched->value,
                    ['marketplace' => $marketplaceValue]
                );

                Prometheus::addCounter('sync_duration_seconds_sum')->inc($duration, [$marketplaceValue, 'dispatch_'.$operationType->value]);
                Prometheus::addCounter('sync_duration_seconds_count')->inc(1, [$marketplaceValue, 'dispatch_'.$operationType->value]);
                Prometheus::addCounter('sync_dispatched_total')->inc(1, [$marketplaceValue, $operationType->value]);
            } catch (Throwable $e) {
                Prometheus::addCounter('sync_errors_total')->inc(1, [$marketplaceValue, 'dispatch_error']);

                $logger->log(
                    $this->organizationId,
                    sprintf('Failed to dispatch %s sync for %s: ', $operationLabel, $marketplaceValue).$e->getMessage(),
                    ActivityLogTypeEnum::SyncError->value,
                    ['marketplace' => $marketplaceValue]
                );

                throw $e;
            }
        }
    }

    public function failed(Throwable $exception): void
    {
        $operationLabel = $this->getOperationLabel();
        $message = sprintf('%s synchronization dispatch failed: %s', $operationLabel, $exception->getMessage());

        Log::error($message, [
            'exception' => $exception,
            'organization_id' => $this->organizationId,
            'marketplace' => $this->marketplace?->value,
        ]);

        resolve(ActivityLoggerInterface::class)->log(
            $this->organizationId,
            $message,
            ActivityLogTypeEnum::SyncError->value,
            ['marketplace' => $this->marketplace?->value]
        );

        event(new SyncFailed($this->organizationId, $this->getOperationType()->value, $message));
    }
}
