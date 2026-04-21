<?php

declare(strict_types=1);

namespace App\Modules\Product\Infrastructure\Jobs;

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

class SyncProductsJob implements ShouldQueue
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

        $logger->log($this->organizationId, 'Started products synchronization dispatch to Go Service', 'sync_start');

        $connections = MarketplaceConnection::where('organization_id', $this->organizationId)
            ->where('is_active', true)
            ->when($this->marketplace, fn ($q) => $q->where('marketplace', $this->marketplace))
            ->get();

        foreach ($connections as $connection) {
            $startTime = microtime(true);
            $marketplaceValue = $connection->marketplace->value;

            try {
                // Prepare credentials payload based on marketplace
                $payload = [
                    'connection_id' => $connection->id,
                    ...$connection->credentials,
                ];

                // Dispatch to Go Sync Service via RabbitMQ
                $dispatchAction->execute(
                    $this->organizationId,
                    $connection->marketplace,
                    'products',
                    $payload
                );

                $duration = microtime(true) - $startTime;

                $logger->log($this->organizationId, 'Products sync task dispatched for '.$connection->marketplace->label(), 'sync_dispatched', [
                    'marketplace' => $marketplaceValue,
                ]);

                // Record Prometheus metrics for dispatch duration
                Prometheus::addCounter('sync_duration_seconds_sum')
                    ->inc($duration, [$marketplaceValue, 'dispatch_products']);

                Prometheus::addCounter('sync_duration_seconds_count')
                    ->inc(1, [$marketplaceValue, 'dispatch_products']);

                Prometheus::addCounter('sync_dispatched_total')
                    ->inc(1, [$marketplaceValue, 'products']);
            } catch (Throwable $e) {
                // Record error to Prometheus
                Prometheus::addCounter('sync_errors_total')
                    ->inc(1, [$marketplaceValue, 'dispatch_error']);

                $logger->log($this->organizationId, sprintf('Failed to dispatch products sync for %s: ', $marketplaceValue).$e->getMessage(), 'sync_error', [
                    'marketplace' => $marketplaceValue,
                ]);
            }
        }
    }

    public function failed(Throwable $exception): void
    {
        resolve(ActivityLoggerInterface::class)->log(
            $this->organizationId,
            'Products synchronization dispatch failed: '.$exception->getMessage(),
            'sync_error'
        );

        event(new SyncFailed(
            $this->organizationId,
            'products',
            'Products synchronization dispatch failed: '.$exception->getMessage()
        ));
    }
}
