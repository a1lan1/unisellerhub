<?php

declare(strict_types=1);

namespace App\Modules\PriceAnalysis\Infrastructure\Jobs;

use App\Modules\Report\Application\Actions\RequestReportAction;
use App\Modules\Report\Domain\Data\RequestReportTaskData;
use App\Modules\Report\Domain\Enums\ReportTypeEnum;
use App\Modules\Report\Domain\ValueObjects\BatchId;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

#[Backoff(5)]
#[Tries(3)]
final class GenerateReportForBatchJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly string $batchId) {}

    public function handle(RequestReportAction $requestReportAction): void
    {
        $batchKey = 'price_analysis:batch:'.$this->batchId;
        $resultsKey = 'price_analysis:results:'.$this->batchId;

        $batchMetadata = json_decode(Redis::get($batchKey) ?: '{}', true);
        $priceAnalysisResults = json_decode(Redis::get($resultsKey) ?: '[]', true);

        if (empty($batchMetadata) || empty($priceAnalysisResults)) {
            Log::error('Cannot generate report: batch metadata or price analysis results not found in Redis.', ['batch_id' => $this->batchId]);
            // Update batch status to indicate failure
            if (! empty($batchMetadata)) {
                $batchMetadata['status'] = 'report_generation_failed';
                Redis::set($batchKey, json_encode($batchMetadata));
            }

            return;
        }

        $organizationId = $batchMetadata['organization_id'];

        // Format data for report_generator
        $reportData = [];
        foreach ($priceAnalysisResults as $item) {
            $reportData[] = [
                'SKU' => $item['data']['sku'] ?? 'N/A',
                'Product ID' => $item['data']['product_id'] ?? 'N/A',
                'Marketplace' => $item['marketplace'] ?? 'N/A',
                'Avg Daily Sales' => $item['data']['stats']['avg_daily_sales'] ?? 0.0,
                'Days Left' => $item['data']['stats']['days_left'] ?? 0,
                'Trend' => $item['data']['stats']['trend'] ?? 'N/A',
            ];
        }

        if ($reportData === []) {
            Log::warning('No data to generate report for batch.', ['batch_id' => $this->batchId]);
            // Update batch status to indicate no data for report
            $batchMetadata['status'] = 'no_data_for_report';
            Redis::set($batchKey, json_encode($batchMetadata));

            return;
        }

        $taskData = new RequestReportTaskData(
            organization_id: $organizationId,
            report_type: ReportTypeEnum::PRICE_ANALYSIS,
            data: $reportData,
            batch_id: new BatchId($this->batchId),
        );

        $requestReportAction->execute($taskData);

        Log::info('Report generation task dispatched for price analysis batch.', ['batch_id' => $this->batchId]);

        // Clean up temporary price analysis results from Redis
        Redis::del($resultsKey);
    }
}
