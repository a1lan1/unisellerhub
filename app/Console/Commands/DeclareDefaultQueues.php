<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Modules\Shared\Domain\Enums\QueueNameEnum;
use App\Modules\Shared\Domain\Interfaces\RabbitMQConnectionInterface;
use Exception;
use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class DeclareDefaultQueues extends Command
{
    /**
     * @var string
     */
    protected $signature = 'queue:declare-queues';

    /**
     * @var string
     */
    protected $description = 'Declare queues that Horizon will listen to, to prevent "not_found" errors.';

    public function handle(RabbitMQConnectionInterface $connectionService): void
    {
        $queueNames = [
            QueueNameEnum::Default->value,
            QueueNameEnum::HighPriority->value,
            QueueNameEnum::LowPriority->value,
            QueueNameEnum::MeilisearchTasks->value,
        ];

        $connection = $connectionService->connect();

        if (! $connection instanceof AMQPStreamConnection) {
            $this->error('Failed to declare RabbitMQ queues: Could not establish connection.');

            return;
        }

        try {
            $channel = $connection->channel();

            foreach ($queueNames as $queueName) {
                $channel->queue_declare(
                    queue: $queueName,
                    passive: false,
                    durable: true,
                    exclusive: false,
                    auto_delete: false
                );

                $this->info(sprintf("Declare queue: '%s'.", $queueName));
            }

            $channel->close();
            $connection->close();
        } catch (Exception $exception) {
            $this->error('Error declaring RabbitMQ queues: '.$exception->getMessage());
        }
    }
}
