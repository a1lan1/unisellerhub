<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Infrastructure\Jobs;

use App\Modules\Activity\Domain\Interfaces\ActivityLoggerInterface;
use App\Modules\Inventory\Domain\Events\InventorySynced;
use App\Modules\Inventory\Domain\Models\Inventory;
use App\Modules\Shared\Application\Services\TenantManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\Prometheus\Facades\Prometheus;

class SyncBulkInventoryJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $organizationId,
        public array $inventoryIds
    ) {}

    public function handle(TenantManager $tenantManager, ActivityLoggerInterface $logger): void
    {
        $tenantManager->setOrganizationId($this->organizationId);

        $items = Inventory::whereIn('id', $this->inventoryIds)
            ->whereHas('listing.product', fn ($q) => $q->where('organization_id', $this->organizationId))
            ->get();

        if ($items->isEmpty()) {
            return;
        }

        $logger->log($this->organizationId, 'Started bulk inventory pull for '.$items->count().' items', 'sync_start');

        $startTime = microtime(true);

        // Simulation: update timestamps
        foreach ($items as $item) {
            $item->touch();
        }

        $duration = microtime(true) - $startTime;

        // Record metrics for bulk inventory processing
        Prometheus::addCounter('sync_duration_seconds_sum')
            ->inc($duration, ['internal', 'bulk_inventory']);

        Prometheus::addCounter('sync_duration_seconds_count')
            ->inc(1, ['internal', 'bulk_inventory']);

        $logger->log($this->organizationId, 'Bulk inventory pull completed for '.$items->count().' items', 'sync_success');

        event(new InventorySynced($this->organizationId));
    }
}
