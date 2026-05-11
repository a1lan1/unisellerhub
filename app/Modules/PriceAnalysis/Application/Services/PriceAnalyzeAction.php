<?php

declare(strict_types=1);

namespace App\Modules\PriceAnalysis\Application\Services;

use App\Modules\PriceAnalysis\Domain\Data\PriceAnalysisTaskData;
use App\Modules\Shared\Domain\Enums\QueueNameEnum;
use Illuminate\Support\Facades\Queue;

final readonly class PriceAnalyzeAction
{
    /**
     * @param  array<array-key, PriceAnalysisTaskData>  $batchData
     */
    public function execute(array $batchData): void
    {
        // Convert array of DTOs to array of arrays for JSON encoding
        $payload = array_map(fn (PriceAnalysisTaskData $data): array => $data->toArray(), $batchData);

        Queue::connection('rabbitmq')->pushRaw(
            json_encode([
                'id' => null,
                'displayName' => null,
                'payload' => $payload,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            QueueNameEnum::PriceTasks->value
        );
    }
}
