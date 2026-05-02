<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Infrastructure\Jobs;

use App\Modules\Marketplace\Domain\Enums\SyncOperationTypeEnum;
use App\Modules\Shared\Application\Jobs\AbstractMarketplaceSyncJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncInventoryJob extends AbstractMarketplaceSyncJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected function getOperationType(): SyncOperationTypeEnum
    {
        return SyncOperationTypeEnum::Inventory;
    }

    protected function getOperationLabel(): string
    {
        return 'inventory';
    }
}
