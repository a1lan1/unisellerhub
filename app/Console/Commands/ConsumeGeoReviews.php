<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Modules\Geo\Domain\Data\ReviewData;
use App\Modules\Geo\Infrastructure\Jobs\ProcessGeoReview;
use App\Modules\Shared\Domain\Enums\QueueNameEnum;
use App\Modules\Shared\Domain\Interfaces\RabbitMQConnectionInterface;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

class ConsumeGeoReviews extends Command
{
    protected $signature = 'rabbitmq:consume-geo-reviews';

    protected $description = 'Consume geo reviews from RabbitMQ queue';

    /**
     * @throws Exception
     */
    public function handle(RabbitMQConnectionInterface $rabbitMQConnectionService): int
    {
        $this->info('Starting Geo Reviews Consumer...');

        $connection = $rabbitMQConnectionService->connect();
        if (! $connection instanceof AMQPStreamConnection) {
            $this->error('Failed to establish RabbitMQ connection.');

            return 1;
        }

        $channel = $connection->channel();
        $queueName = QueueNameEnum::GeoReviews->value;

        $channel->queue_declare($queueName, false, true, false, false);

        $this->info(sprintf("Waiting for messages in queue '%s'.", $queueName));

        $callback = function (AMQPMessage $msg): void {
            $body = $msg->getBody();
            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to decode JSON message from RabbitMQ', ['body' => $body]);
                $msg->ack();

                return;
            }

            try {
                $reviewData = ReviewData::from($data);

                dispatch(new ProcessGeoReview($reviewData));

                Log::info('Dispatched ProcessGeoReview job for external_id: '.$reviewData->externalId);
                $this->info(sprintf('Processed review from %s with external ID %s', $reviewData->source->value, $reviewData->externalId));

                $msg->ack();
            } catch (Throwable $throwable) {
                Log::error('Error processing geo review from RabbitMQ', [
                    'exception' => $throwable->getMessage(),
                    'body' => $body,
                ]);

                $this->error('Error processing geo review from RabbitMQ: '.$throwable->getMessage());
                $msg->nack(true); // Requeue
            }
        };

        $channel->basic_consume($queueName, '', false, false, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();

        return 0;
    }
}
