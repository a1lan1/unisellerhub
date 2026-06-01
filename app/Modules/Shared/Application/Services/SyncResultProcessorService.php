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

class SyncResultProcessorService implements SyncResultProcessorInterface
{
    public function __construct(
        private readonly TenantManager $tenantManager,
        private readonly MarketplaceConnectionRepositoryInterface $connectionRepository,
        private readonly MarketplaceDataMapper $marketplaceDataMapper,
        private readonly SaveMarketplaceStockAction $saveMarketplaceStockAction,
        private readonly SaveMarketplaceOrdersAction $saveMarketplaceOrdersAction,
        private readonly SaveMarketplaceProductsAction $saveMarketplaceProductsAction,
    ) {}

    /**
     * @throws Throwable
     */
    public function processSuccess(array $data): void
    {
        $operation = $data['operation'] ?? null;
        $organizationId = (int) ($data['organization_id'] ?? 0);

        if (empty($data['marketplace'])) {
            Log::warning('marketplace empty');

            return;
        }

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
        $operation = $data['operation'] ?? null;
        $marketplace = $data['marketplace'];

        Log::error(sprintf('Microservice Error for %s [%s]: %s', $marketplace, $operation, $data['error_message']), $data);

        // Record error metric
        Prometheus::addCounter('sync_errors_total')->inc(1, [$marketplace, 'service_error']);
    }
}
