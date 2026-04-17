<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Modules\Inventory\Application\Actions\SaveMarketplaceStockAction;
use App\Modules\Marketplace\Application\Mappers\MarketplaceDataMapper;
use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use App\Modules\Marketplace\Domain\Repositories\MarketplaceConnectionRepositoryInterface;
use App\Modules\Order\Application\Actions\SaveMarketplaceOrdersAction;
use App\Modules\Product\Application\Actions\SaveMarketplaceProductsAction;
use App\Modules\Shared\Application\Services\TenantManager;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Sleep;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Spatie\Prometheus\Facades\Prometheus;
use Throwable;

final class ConsumeSyncResultsCommand extends Command
{
    protected $signature = 'app:consume-sync-results';

    protected $description = 'Consume marketplace microservice results from RabbitMQ and update Database';

    /**
     * @throws Exception
     */
    public function handle(
        TenantManager $tenantManager,
        MarketplaceConnectionRepositoryInterface $connectionRepository
    ): int {
        $this->info('Starting Result Consumer...');

        $connection = $this->connectToRabbitMQ();
        if (! $connection instanceof AMQPStreamConnection) {
            return 1;
        }

        $channel = $connection->channel();
        $channel->queue_declare(
            queue: 'sync.results',
            passive: true,
            durable: false,
            exclusive: false,
            auto_delete: false
        );

        $callback = function (AMQPMessage $msg) use ($tenantManager, $connectionRepository): void {
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
                    $this->handleSuccess($data, $tenantManager, $connectionRepository);
                } else {
                    $this->handleFailure($data);
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
            'sync.results',
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

    private function connectToRabbitMQ(): ?AMQPStreamConnection
    {
        $attempts = 0;
        $maxAttempts = 10;

        while ($attempts < $maxAttempts) {
            try {
                return new AMQPStreamConnection(
                    config('queue.connections.rabbitmq.hosts.0.host'),
                    config('queue.connections.rabbitmq.hosts.0.port'),
                    config('queue.connections.rabbitmq.hosts.0.user'),
                    config('queue.connections.rabbitmq.hosts.0.password'),
                    config('queue.connections.rabbitmq.hosts.0.vhost')
                );
            } catch (Exception) {
                $attempts++;
                $this->warn(sprintf('Failed to connect to RabbitMQ (attempt %d/%d). Retrying in 5 seconds...', $attempts, $maxAttempts));
                Sleep::sleep(5);
            }
        }

        $this->error(sprintf('Could not connect to RabbitMQ after %d attempts.', $maxAttempts));

        return null;
    }

    /**
     * @throws Throwable
     */
    private function handleSuccess(
        array $data,
        TenantManager $tenantManager,
        MarketplaceConnectionRepositoryInterface $connectionRepository
    ): void {
        $operation = $data['operation'];
        $organizationId = (int) $data['organization_id'];
        $marketplace = MarketplaceEnum::from($data['marketplace']);

        $tenantManager->setOrganizationId($organizationId);

        $this->info(sprintf('Processing success result for %s (Org: %d)', $operation, $organizationId));

        $connection = $connectionRepository->findByOrganizationAndMarketplace($organizationId, $marketplace);

        if (! $connection instanceof MarketplaceConnection) {
            Log::warning(sprintf('Connection not found for Org: %d, Marketplace: %s', $organizationId, $marketplace->value));

            return;
        }

        switch ($operation) {
            case 'inventory':
                $stocks = resolve(MarketplaceDataMapper::class)->mapStocks($marketplace, (array) ($data['data'] ?? []));
                resolve(SaveMarketplaceStockAction::class)->execute($connection, $stocks);
                break;
            case 'orders':
                $orders = resolve(MarketplaceDataMapper::class)->mapOrders($marketplace, (array) ($data['data'] ?? []));
                resolve(SaveMarketplaceOrdersAction::class)->execute($connection, $orders);
                break;
            case 'products':
                $products = resolve(MarketplaceDataMapper::class)->mapProducts($marketplace, (array) ($data['data'] ?? []));
                resolve(SaveMarketplaceProductsAction::class)->execute($connection, $products);
                break;
        }

        // Record metrics
        $duration = $data['duration'] ?? 0;
        $labels = [$marketplace->value, $operation];
        Prometheus::addCounter('sync_duration_seconds_sum')->inc($duration, $labels);
        Prometheus::addCounter('sync_duration_seconds_count')->inc(1, $labels);
    }

    private function handleFailure(array $data): void
    {
        $marketplace = $data['marketplace'];
        $operation = $data['operation'];

        Log::error(sprintf('Microservice Error for %s [%s]: %s', $marketplace, $operation, $data['error_message']), $data);

        // Record error metric
        Prometheus::addCounter('sync_errors_total')->inc(1, [$marketplace, 'service_error']);
    }
}
