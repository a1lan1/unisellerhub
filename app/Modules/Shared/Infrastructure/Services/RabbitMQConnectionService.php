<?php

declare(strict_types=1);

namespace App\Modules\Shared\Infrastructure\Services;

use App\Modules\Shared\Domain\Interfaces\RabbitMQConnectionInterface;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Sleep;
use PhpAmqpLib\Connection\AMQPStreamConnection;

final class RabbitMQConnectionService implements RabbitMQConnectionInterface
{
    private const int MAX_ATTEMPTS = 10;

    private const int RETRY_DELAY_SECONDS = 5;

    public function connect(): ?AMQPStreamConnection
    {
        $attempts = 0;

        while ($attempts < self::MAX_ATTEMPTS) {
            try {
                return new AMQPStreamConnection(
                    config('queue.connections.rabbitmq.hosts.0.host'),
                    config('queue.connections.rabbitmq.hosts.0.port'),
                    config('queue.connections.rabbitmq.hosts.0.user'),
                    config('queue.connections.rabbitmq.hosts.0.password'),
                    config('queue.connections.rabbitmq.hosts.0.vhost')
                );
            } catch (Exception $e) {
                $attempts++;
                Log::warning(sprintf('Failed to connect to RabbitMQ (attempt %d/%d). Retrying in %d seconds... Error: %s', $attempts, self::MAX_ATTEMPTS, self::RETRY_DELAY_SECONDS, $e->getMessage()));
                Sleep::sleep(self::RETRY_DELAY_SECONDS);
            }
        }

        Log::error(sprintf('Could not connect to RabbitMQ after %d attempts.', self::MAX_ATTEMPTS));

        return null;
    }
}
