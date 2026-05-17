<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Modules\PriceAnalysis\Application\Services\PriceAnalysisSyncResultProcessor;
use App\Modules\Shared\Domain\Enums\QueueNameEnum;
use App\Modules\Shared\Domain\Interfaces\RabbitMQConnectionInterface;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

final class ProcessPriceAnalysisSyncResultsCommand extends Command
{
    protected $signature = 'app:process-price-analysis-sync-results';

    protected $description = 'Consume price analysis and report generation results from RabbitMQ and process them.';

    /**
     * @throws Exception
     */
    public function handle(
        RabbitMQConnectionInterface $rabbitMQConnectionService,
        PriceAnalysisSyncResultProcessor $processor
    ): int {
        $this->info('Starting Price Analysis Result Consumer...');

        $connection = $rabbitMQConnectionService->connect();
        if (! $connection instanceof AMQPStreamConnection) {
            $this->error('Failed to establish RabbitMQ connection.');

            return 1;
        }

        $channel = $connection->channel();
        $channel->queue_declare(
            queue: QueueNameEnum::ReportResults->value,
            passive: true,
            durable: false,
            exclusive: false,
            auto_delete: false
        );

        $callback = function (AMQPMessage $msg) use ($processor): void {
            $data = json_decode($msg->getBody(), true);
            $operation = $data['operation'] ?? 'unknown';
            $batchId = $data['batch_id'] ?? 'N/A';
            $organizationId = $data['organization_id'] ?? 'N/A';

            $this->info(sprintf('Received result: [%s] for Batch: %s, Org: %s', $operation, $batchId, $organizationId));

            try {
                if ($data['status'] === 'success') {
                    $processor->processSuccess($data);
                } else {
                    $processor->processFailure($data);
                }

                $msg->ack();
            } catch (Throwable $throwable) {
                Log::error('Error processing price analysis result: '.$throwable->getMessage(), [
                    'exception' => $throwable,
                    'data' => $data,
                ]);
                $this->error('Error processing price analysis result: '.$throwable->getMessage());
                $msg->nack(true); // Requeue
            }
        };

        $channel->basic_consume(
            QueueNameEnum::ReportResults->value,
            '',
            false,
            false,
            false,
            false,
            $callback
        );

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();

        return 0;
    }
}
