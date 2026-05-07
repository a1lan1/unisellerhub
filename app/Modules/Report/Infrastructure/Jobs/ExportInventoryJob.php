<?php

declare(strict_types=1);

namespace App\Modules\Report\Infrastructure\Jobs;

use App\Modules\Product\Domain\Data\ProductListingsFilterData;
use App\Modules\Report\Domain\Events\ExportReadyEvent;
use App\Modules\Report\Domain\Exports\InventoryExport;
use App\Modules\Shared\Application\Services\TenantManager;
use App\Modules\User\Application\Services\NotificationService;
use App\Modules\User\Domain\Data\NotificationData;
use App\Modules\User\Domain\Enums\NotificationTypeEnum;
use App\Modules\User\Domain\Models\User;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Maatwebsite\Excel\Facades\Excel;

class ExportInventoryJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly User $user,
        private readonly ?ProductListingsFilterData $filters
    ) {}

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function handle(NotificationService $notificationService, TenantManager $tenantManager): void
    {
        $fileName = 'inventory_export_'.now()->format('Y-m-d_H-i-s').'.xlsx';

        try {
            $tenantManager->setOrganizationId($this->user->organization_id);

            Excel::store(new InventoryExport($this->user->organization_id, $this->filters), $fileName, 'local');

            $fileUrl = URL::temporarySignedRoute(
                'exports.download',
                now()->addMinutes(30),
                ['filename' => $fileName]
            );

            // Dispatch broadcast event for immediate download
            event(new ExportReadyEvent($this->user->id, $fileUrl, 'inventory'));

            // Send system notification
            $notificationService->sendToUser(
                $this->user,
                new NotificationData(
                    title: 'Inventory Export Ready',
                    message: 'Your inventory export file is ready for download.',
                    type: NotificationTypeEnum::SUCCESS,
                    actionUrl: $fileUrl,
                    icon: 'mdi-microsoft-excel'
                )
            );

            Log::info(sprintf('Inventory export completed for user %d. File saved privately as: %s', $this->user->id, $fileName));
        } catch (Exception $exception) {
            Log::error(sprintf('Inventory export failed for user %d. Error: %s', $this->user->id, $exception->getMessage()), [
                'exception' => $exception,
                'user_id' => $this->user->id,
                'file_name' => $fileName,
            ]);

            $notificationService->sendToUser(
                $this->user,
                new NotificationData(
                    title: 'Inventory Export Failed',
                    message: 'Failed to generate your inventory export file. Please try again or contact support.',
                    type: NotificationTypeEnum::ERROR,
                    icon: 'mdi-alert-circle'
                )
            );

            throw $exception;
        }
    }
}
