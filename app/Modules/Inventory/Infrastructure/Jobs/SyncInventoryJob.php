<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Infrastructure\Jobs;

use App\Modules\Activity\Domain\Interfaces\ActivityLoggerInterface;
use App\Modules\Marketplace\Application\Actions\DispatchSyncTaskToGoAction;
use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Marketplace\Domain\Events\SyncFailed;
use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use App\Modules\Shared\Application\Services\TenantManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\Prometheus\Facades\Prometheus;
use Throwable;

class SyncInventoryJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $organizationId,
        public ?MarketplaceEnum $marketplace = null
    ) {}

    /**
     * Handle the sync process by dispatching task to Go Sync Service.
     */
    public function handle(
        DispatchSyncTaskToGoAction $dispatchAction,
        TenantManager $tenantManager,
        ActivityLoggerInterface $logger
    ): void {
        $tenantManager->setOrganizationId($this->organizationId);

        $logger->log($this->organizationId, 'Started inventory synchronization dispatch to Go Service', 'sync_start');

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
                    $this->organizationId,
                    $connection->marketplace,
                    'inventory',
                    [
                        'connection_id' => $connection->id,
                        ...$connection->credentials,
                    ]
                );

                $duration = microtime(true) - $startTime;

                $logger->log($this->organizationId, 'Inventory sync task dispatched for '.$connection->marketplace->label(), 'sync_dispatched', [
                    'marketplace' => $marketplaceValue,
                ]);

                Prometheus::addCounter('sync_duration_seconds_sum')
                    ->inc($duration, [$marketplaceValue, 'dispatch_inventory']);

                Prometheus::addCounter('sync_duration_seconds_count')
                    ->inc(1, [$marketplaceValue, 'dispatch_inventory']);

                Prometheus::addCounter('sync_dispatched_total')
                    ->inc(1, [$marketplaceValue, 'inventory']);

            } catch (Throwable $e) {
                Prometheus::addCounter('sync_errors_total')
                    ->inc(1, [$marketplaceValue, 'dispatch_error']);

                $logger->log($this->organizationId, sprintf('Failed to dispatch inventory sync for %s: ', $marketplaceValue).$e->getMessage(), 'sync_error', [
                    'marketplace' => $marketplaceValue,
                ]);
            }
        }
    }

    public function failed(Throwable $exception): void
    {
        resolve(ActivityLoggerInterface::class)->log(
            $this->organizationId,
            'Inventory synchronization dispatch failed: '.$exception->getMessage(),
            'sync_error'
        );

        event(new SyncFailed(
            $this->organizationId,
            'inventory',
            'Inventory synchronization dispatch failed: '.$exception->getMessage()
        ));
    }
}
