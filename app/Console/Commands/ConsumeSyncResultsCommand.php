<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Modules\Shared\Domain\Enums\QueueNameEnum;
use App\Modules\Shared\Domain\Interfaces\RabbitMQConnectionInterface;
use App\Modules\Shared\Domain\Interfaces\SyncResultProcessorInterface;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

final class ConsumeSyncResultsCommand extends Command
{
    protected $signature = 'app:consume-sync-results';

    protected $description = 'Consume marketplace microservice results from RabbitMQ and update Database';

    /**
     * @throws Exception
     */
    public function handle(
        RabbitMQConnectionInterface $rabbitMQConnectionService,
        SyncResultProcessorInterface $syncResultProcessorService
    ): int {
        $this->info('Starting Result Consumer...');

        $connection = $rabbitMQConnectionService->connect();
        if (! $connection instanceof AMQPStreamConnection) {
            return 1;
        }

        $channel = $connection->channel();
        $channel->queue_declare(
            queue: QueueNameEnum::SyncResults->value,
            passive: true,
            durable: false,
            exclusive: false,
            auto_delete: false
        );

        $callback = function (AMQPMessage $msg) use ($syncResultProcessorService): void {
            $data = json_decode($msg->getBody(), true);
            $this->info(sprintf('Received result: [%s] for Org: %s from Marketplace: %s', $data['operation'], $data['organization_id'], $data['marketplace']));

            // DEBUG
            Log::debug('RAW DATA FROM GO:', [
                'marketplace' => $data['marketplace'],
                'operation' => $data['operation'],
                'count' => is_array($data['data']) ? count($data['data']) : 0,
                'sample' => is_array($data['data']) ? array_slice($data['data'], 0, 1) : $data['data'],
            ]);

            try {
                if ($data['status'] === 'success') {
                    $syncResultProcessorService->processSuccess($data);
                } else {
                    $syncResultProcessorService->processFailure($data);
                }

                $msg->ack();
            } catch (Throwable $throwable) {
                Log::error('Error processing result: '.$throwable->getMessage(), [
                    'exception' => $throwable,
                    'data' => $data,
                ]);
                $this->error('Error processing result: '.$throwable->getMessage());
                $msg->nack(true); // Requeue
            }
        };

        $channel->basic_consume(
            QueueNameEnum::SyncResults->value,
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
