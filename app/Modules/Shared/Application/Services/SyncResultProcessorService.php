<?php

declare(strict_types=1);

namespace App\Modules\Shared\Application\Services;

use App\Modules\Inventory\Application\Actions\SaveMarketplaceStockAction;
use App\Modules\Marketplace\Application\Mappers\MarketplaceDataMapper;
use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use App\Modules\Marketplace\Domain\Repositories\MarketplaceConnectionRepositoryInterface;
use App\Modules\Order\Application\Actions\SaveMarketplaceOrdersAction;
use App\Modules\Product\Application\Actions\SaveMarketplaceProductsAction;
use App\Modules\Shared\Domain\Interfaces\SyncResultProcessorInterface;
use Illuminate\Support\Facades\Log;
use Spatie\Prometheus\Facades\Prometheus;
use Throwable;

final readonly class SyncResultProcessorService implements SyncResultProcessorInterface
{
    public function __construct(
        private TenantManager $tenantManager,
        private MarketplaceConnectionRepositoryInterface $connectionRepository,
        private MarketplaceDataMapper $marketplaceDataMapper,
        private SaveMarketplaceStockAction $saveMarketplaceStockAction,
        private SaveMarketplaceOrdersAction $saveMarketplaceOrdersAction,
        private SaveMarketplaceProductsAction $saveMarketplaceProductsAction,
    ) {}

    /**
     * @throws Throwable
     */
    public function processSuccess(array $data): void
    {
        if (empty($data['marketplace'])) {
            Log::warning('marketplace empty');
            return;
        }

        $operation = $data['operation'];
        $organizationId = (int) $data['organization_id'];
        $marketplace = MarketplaceEnum::tryFrom($data['marketplace']);

        $this->tenantManager->setOrganizationId($organizationId);

        Log::info(sprintf('Processing success result for %s (Org: %d)', $operation, $organizationId));

        $connection = $this->connectionRepository->findByOrganizationAndMarketplace($organizationId, $marketplace);

        if (! $connection instanceof MarketplaceConnection) {
            Log::warning(sprintf('Connection not found for Org: %d, Marketplace: %s', $organizationId, $marketplace->value));

            return;
        }

        switch ($operation) {
            case 'inventory':
                $stocks = $this->marketplaceDataMapper->mapStocks($marketplace, (array) ($data['data'] ?? []));
                $this->saveMarketplaceStockAction->execute($connection, $stocks);
                break;
            case 'orders':
                $orders = $this->marketplaceDataMapper->mapOrders($marketplace, (array) ($data['data'] ?? []));
                $this->saveMarketplaceOrdersAction->execute($connection, $orders);
                break;
            case 'products':
                $products = $this->marketplaceDataMapper->mapProducts($marketplace, (array) ($data['data'] ?? []));
                $this->saveMarketplaceProductsAction->execute($connection, $products);
                break;
        }

        // Record metrics
        $duration = $data['duration'] ?? 0;
        $labels = [$marketplace->value, $operation];
        Prometheus::addCounter('sync_duration_seconds_sum')->inc($duration, $labels);
        Prometheus::addCounter('sync_duration_seconds_count')->inc(1, $labels);
    }

    public function processFailure(array $data): void
    {
        $marketplace = $data['marketplace'];
        $operation = $data['operation'];

        Log::error(sprintf('Microservice Error for %s [%s]: %s', $marketplace, $operation, $data['error_message']), $data);

        // Record error metric
        Prometheus::addCounter('sync_errors_total')->inc(1, [$marketplace, 'service_error']);
    }
}
