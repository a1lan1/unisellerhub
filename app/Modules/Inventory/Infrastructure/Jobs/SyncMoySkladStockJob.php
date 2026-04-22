<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Infrastructure\Jobs;

use App\Modules\Activity\Domain\Interfaces\ActivityLoggerInterface;
use App\Modules\Inventory\Domain\Actions\SyncMoySkladStockToMarketplacesAction;
use App\Modules\Inventory\Domain\Events\InventorySynced;
use App\Modules\Marketplace\Domain\Events\SyncFailed;
use App\Modules\Shared\Application\Services\TenantManager;
use App\Modules\User\Domain\Data\NotificationData;
use App\Modules\User\Domain\Enums\NotificationTypeEnum;
use App\Modules\User\Domain\Interfaces\NotificationServiceInterface;
use App\Modules\User\Domain\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\Prometheus\Facades\Prometheus;
use Throwable;

class SyncMoySkladStockJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $organizationId) {}

    /**
     * Handle the sync process.
     *
     * @throws Throwable
     */
    public function handle(
        SyncMoySkladStockToMarketplacesAction $syncAction,
        TenantManager $tenantManager,
        NotificationServiceInterface $notificationService,
        ActivityLoggerInterface $logger
    ): void {
        $tenantManager->setOrganizationId($this->organizationId);

        $logger->log($this->organizationId, 'Started MoySklad inventory synchronization', 'sync_start');

        $startTime = microtime(true);

        try {
            $syncAction->execute($this->organizationId);

            $duration = microtime(true) - $startTime;

            // Record Prometheus metrics for MoySklad
            Prometheus::addCounter('sync_duration_seconds_sum')
                ->inc($duration, ['ms', 'inventory']);

            Prometheus::addCounter('sync_duration_seconds_count')
                ->inc(1, ['ms', 'inventory']);

            event(new InventorySynced($this->organizationId));

            $logger->log($this->organizationId, 'MoySklad inventory successfully synced to all marketplaces', 'sync_success', [
                'marketplace' => 'ms',
            ]);

            $org = Organization::find($this->organizationId);
            if ($org) {
                $notificationService->sendToOrganization(
                    $org,
                    new NotificationData(
                        title: 'Sync Success',
                        message: 'Marketplace stocks successfully synchronized with MoySklad.',
                        type: NotificationTypeEnum::SUCCESS,
                        icon: 'mdi-check-circle'
                    )
                );
            }
        } catch (Throwable $throwable) {
            // Record error to Prometheus
            Prometheus::addCounter('sync_errors_total')
                ->inc(1, ['ms', 'api']);

            throw $throwable; // Rethrow to let failed() handle logging
        }
    }

    public function failed(Throwable $exception): void
    {
        resolve(ActivityLoggerInterface::class)->log(
            $this->organizationId,
            'MoySklad synchronization failed: '.$exception->getMessage(),
            'sync_error'
        );
        event(new SyncFailed($this->organizationId, 'inventory', 'MoySklad synchronization failed: '.$exception->getMessage()));
    }
}
