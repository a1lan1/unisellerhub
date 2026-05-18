<?php

declare(strict_types=1);

namespace App\Modules\Report\Infrastructure\Jobs;

use App\Modules\Report\Domain\Events\ExportReadyEvent;
use App\Modules\Report\Domain\Exports\ProductListingsWithCostsExport;
use App\Modules\Report\Domain\Exports\ProductRevenueExport;
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

class GenerateAnalyticsReportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly User $user,
        private readonly string $reportType,
        private readonly array $reportParams = [],
    ) {}

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function handle(
        NotificationService $notificationService,
        TenantManager $tenantManager
    ): void {
        $fileName = sprintf('analytics_export_%s_', $this->reportType).now()->format('Y-m-d_H-i-s').'.xlsx';

        try {
            $tenantManager->setOrganizationId($this->user->organization_id);

            switch ($this->reportType) {
                case 'product_revenue_analysis':
                    $displayName = 'Product Revenue Analysis Report';
                    $days = (int) ($this->reportParams['days'] ?? 30);
                    $endDate = (string) ($this->reportParams['endDate'] ?? now()->format('Y-m-d'));
                    Excel::store(
                        new ProductRevenueExport($this->user->organization_id, $endDate, $days),
                        $fileName,
                        'reports'
                    );
                    break;
                case 'product_profitability_analysis':
                    $displayName = 'Product Profitability Analysis Report';
                    Excel::store(
                        new ProductListingsWithCostsExport($this->user->organization_id),
                        $fileName,
                        'reports'
                    );
                    break;
                default:
                    throw new Exception('Unknown report type: '.$this->reportType);
            }

            // Changed 'filename' to 'path' to match the route definition
            $fileUrl = URL::temporarySignedRoute(
                'exports.download',
                now()->addMinutes(30),
                ['path' => $fileName]
            );

            // Dispatch broadcast event for immediate download
            event(new ExportReadyEvent($this->user->id, $fileUrl, 'orders'));

            $notificationService->sendToUser(
                $this->user,
                new NotificationData(
                    title: 'Analytics Report Ready',
                    message: sprintf('Your "%s" report is ready for download.', $displayName),
                    type: NotificationTypeEnum::INFO,
                    actionUrl: $fileUrl,
                    icon: 'mdi-chart-line'
                )
            );

            Log::info(sprintf('Analytics report generation requested for user %d, type: %s', $this->user->id, $this->reportType));
        } catch (Exception $exception) {
            Log::error(sprintf('Analytics report generation failed for user %d, type: %s. Error: %s', $this->user->id, $this->reportType, $exception->getMessage()), [
                'exception' => $exception,
                'user_id' => $this->user->id,
                'report_type' => $this->reportType,
            ]);

            $notificationService->sendToUser(
                $this->user,
                new NotificationData(
                    title: 'Analytics Report Failed',
                    message: 'Failed to request your analytics report. Please try again.',
                    type: NotificationTypeEnum::ERROR,
                    icon: 'mdi-alert-circle'
                )
            );

            throw $exception;
        }
    }
}
