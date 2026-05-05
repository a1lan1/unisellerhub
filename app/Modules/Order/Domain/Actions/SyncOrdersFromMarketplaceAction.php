<?php

declare(strict_types=1);

namespace App\Modules\Order\Domain\Actions;

use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use App\Modules\Marketplace\Infrastructure\Factories\MarketplaceClientFactory;
use App\Modules\Order\Application\Actions\SaveMarketplaceOrdersAction;
use Throwable;

final readonly class SyncOrdersFromMarketplaceAction
{
    public function __construct(
        private SaveMarketplaceOrdersAction $saveOrdersAction,
        private MarketplaceClientFactory $marketplaceClient
    ) {}

    /**
     * Syncs orders from a specific marketplace connection.
     *
     * @throws Throwable
     */
    public function execute(MarketplaceConnection $connection): void
    {
        $client = $this->marketplaceClient->make($connection->marketplace, $connection->credentials);

        $orders = $client->getOrders();

        // Delegate saving logic to the dedicated action
        $this->saveOrdersAction->execute($connection, $orders);
    }
}
