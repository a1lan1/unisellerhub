<?php

declare(strict_types=1);

namespace App\Modules\Product\Infrastructure\Jobs;

use App\Modules\Activity\Domain\Interfaces\ActivityLoggerInterface;
use App\Modules\Product\Domain\Events\ProductsSynced;
use App\Modules\Product\Domain\Repositories\ProductListingRepositoryInterface;
use App\Modules\Shared\Application\Services\TenantManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\Prometheus\Facades\Prometheus;

class SyncBulkProductsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $organizationId,
        public array $listingIds
    ) {}

    public function handle(
        TenantManager $tenantManager,
        ActivityLoggerInterface $logger,
        ProductListingRepositoryInterface $repository
    ): void {
        $tenantManager->setOrganizationId($this->organizationId);

        $listings = $repository->getByIdsAndOrganization($this->listingIds, $this->organizationId);

        if ($listings->isEmpty()) {
            return;
        }

        $logger->log($this->organizationId, 'Started bulk synchronization for '.$listings->count().' items', 'sync_start');

        $startTime = microtime(true);

        // Logic: For each unique marketplace in the selection, we could perform a specific sync.
        // For simplicity in this mock-up, we just simulate the sync.
        foreach ($listings as $listing) {
            $listing->update(['last_synced_at' => now()]);
        }

        $duration = microtime(true) - $startTime;

        // Record metrics for bulk product processing
        Prometheus::addCounter('sync_duration_seconds_sum')
            ->inc($duration, ['internal', 'bulk_products']);

        Prometheus::addCounter('sync_duration_seconds_count')
            ->inc(1, ['internal', 'bulk_products']);

        $logger->log($this->organizationId, 'Bulk synchronization completed for '.$listings->count().' items', 'sync_success');

        event(new ProductsSynced($this->organizationId));
    }
}
