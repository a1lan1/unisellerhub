<?php

declare(strict_types=1);

namespace App\Modules\Report\Infrastructure\Jobs;

use App\Modules\Report\Domain\Data\GenerateAnalyticsReportData;
use App\Modules\Report\Domain\Enums\ReportTypeEnum;
use App\Modules\Report\Domain\Events\ExportReadyEvent;
use App\Modules\Report\Domain\Exports\ProductListingsWithCostsExport;
use App\Modules\Report\Domain\Exports\ProductRevenueExport;
use App\Modules\Shared\Application\Services\TenantManager;
use App\Modules\Shared\Domain\ValueObjects\Url;
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
use Illuminate\Support\Facades\URL as URLFacade;
use Maatwebsite\Excel\Facades\Excel;

class GenerateAnalyticsReportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly GenerateAnalyticsReportData $reportData,
    ) {}

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function handle(
        NotificationService $notificationService,
        TenantManager $tenantManager
    ): void {
        $fileName = sprintf('analytics_export_%s_', $this->reportData->reportType->value).now()->format('Y-m-d_H-i-s').'.xlsx';

        try {
            $tenantManager->setOrganizationId($this->user->organization_id);

            switch ($this->reportData->reportType) {
                case ReportTypeEnum::PRODUCT_REVENUE_ANALYSIS:
                    $displayName = 'Product Revenue Analysis Report';
                    $days = $this->reportData->days;
                    $endDate = $this->reportData->endDate;
                    Excel::store(
                        new ProductRevenueExport($this->user->organization_id, $endDate, $days),
                        $fileName,
                        'reports'
                    );
                    break;
                case ReportTypeEnum::PRODUCT_PROFITABILITY_ANALYSIS:
                    $displayName = 'Product Profitability Analysis Report';
                    Excel::store(
                        new ProductListingsWithCostsExport($this->user->organization_id),
                        $fileName,
                        'reports'
                    );
                    break;
                default:
                    throw new Exception('Unknown report type: '.$this->reportData->reportType->value);
            }

            $fileUrl = URLFacade::temporarySignedRoute(
                'exports.download',
                now()->addMinutes(30),
                ['path' => $fileName]
            );

            // Dispatch broadcast event for immediate download
            event(new ExportReadyEvent($this->user->id, $fileUrl, 'analytics_report'));

            $notificationService->sendToUser(
                $this->user,
                new NotificationData(
                    title: 'Analytics Report Ready',
                    message: sprintf('Your "%s" report is ready for download.', $displayName),
                    type: NotificationTypeEnum::INFO,
                    actionUrl: new Url($fileUrl),
                    icon: 'mdi-chart-line'
                )
            );

            Log::info(sprintf('Analytics report generation requested for user %d, type: %s', $this->user->id, $this->reportData->reportType->value));
        } catch (Exception $exception) {
            Log::error(sprintf('Analytics report generation failed for user %d, type: %s. Error: %s', $this->user->id, $this->reportData->reportType->value, $exception->getMessage()), [
                'exception' => $exception,
                'user_id' => $this->user->id,
                'report_type' => $this->reportData->reportType->value,
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
