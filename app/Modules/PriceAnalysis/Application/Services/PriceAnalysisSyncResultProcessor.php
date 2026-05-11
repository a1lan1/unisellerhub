<?php

declare(strict_types=1);

namespace App\Modules\PriceAnalysis\Application\Services;

use App\Modules\PriceAnalysis\Infrastructure\Jobs\GenerateReportForBatchJob;
use App\Modules\PriceAnalysis\Infrastructure\Jobs\NotifyUserOfReportJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\URL;

final readonly class PriceAnalysisSyncResultProcessor
{
    public function processSuccess(array $data): void
    {
        $operation = $data['operation'] ?? 'unknown';
        $batchId = $data['batch_id'] ?? null;

        if (! $batchId) {
            Log::warning('Received price analysis result without batch_id.', ['data' => $data]);

            return;
        }

        $batchKey = 'price_analysis:batch:'.$batchId;
        $batchMetadata = json_decode(Redis::get($batchKey) ?: '{}', true);

        if (empty($batchMetadata)) {
            Log::warning('Received price analysis result for unknown batch_id.', ['batch_id' => $batchId, 'data' => $data]);

            return;
        }

        switch ($operation) {
            case 'price_analysis_batch':
                Log::info('Processing price_analysis_batch result.', ['batch_id' => $batchId]);
                // Store the results from price_analyzer in Redis
                Redis::set('price_analysis:results:'.$batchId, json_encode($data['data']));

                // Update batch status
                $batchMetadata['status'] = 'processed';
                Redis::set($batchKey, json_encode($batchMetadata));

                // Dispatch job to generate report
                dispatch(new GenerateReportForBatchJob($batchId));
                break;

            case 'report_generation':
                Log::info('Processing report_generation result.', ['batch_id' => $batchId]);
                $relativeFilePath = $data['data']['download_url'] ?? null;
                $filename = $data['data']['filename'] ?? null;

                if ($relativeFilePath && $filename) {
                    // Generate a signed temporary URL for download using the named route
                    $signedDownloadUrl = URL::temporarySignedRoute(
                        'exports.download',
                        now()->addMinutes(30),
                        ['path' => $relativeFilePath]
                    );

                    Log::debug('Generated signed download URL:', ['url' => $signedDownloadUrl]);

                    $batchMetadata['status'] = 'completed';
                    $batchMetadata['report_filename'] = $filename;
                    $batchMetadata['download_url'] = $signedDownloadUrl;
                    Redis::set($batchKey, json_encode($batchMetadata));

                    // Dispatch job to notify user
                    dispatch(new NotifyUserOfReportJob($batchId));
                } else {
                    Log::error('Report generation result missing relative file path or filename.', ['batch_id' => $batchId, 'data' => $data]);
                    $batchMetadata['status'] = 'report_failed';
                    Redis::set($batchKey, json_encode($batchMetadata));
                }

                break;

            default:
                Log::warning('Received unhandled operation type in PriceAnalysisSyncResultProcessor.', ['operation' => $operation, 'batch_id' => $batchId, 'data' => $data]);
                break;
        }
    }

    public function processFailure(array $data): void
    {
        $operation = $data['operation'] ?? 'unknown';
        $batchId = $data['batch_id'] ?? null;
        $errorMessage = $data['error_message'] ?? 'Unknown error';

        Log::error('Price analysis microservice error.', [
            'operation' => $operation,
            'batch_id' => $batchId,
            'error_message' => $errorMessage,
            'data' => $data,
        ]);

        if ($batchId) {
            $batchKey = 'price_analysis:batch:'.$batchId;
            $batchMetadata = json_decode(Redis::get($batchKey) ?: '{}', true);
            if (! empty($batchMetadata)) {
                $batchMetadata['status'] = 'failed';
                $batchMetadata['error_message'] = $errorMessage;
                Redis::set($batchKey, json_encode($batchMetadata));
            }
        }
    }
}
