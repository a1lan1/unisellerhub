<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Actions;

use App\Modules\Inventory\Application\Actions\SaveMarketplaceStockAction;
use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use App\Modules\Marketplace\Infrastructure\Factories\MarketplaceClientFactory;
use Illuminate\Support\Facades\Log;

final readonly class SyncStockFromMarketplaceAction
{
    public function __construct(
        private SaveMarketplaceStockAction $saveStockAction,
        private MarketplaceClientFactory $marketplaceClient
    ) {}

    /**
     * Syncs stocks from a specific marketplace connection.
     */
    public function execute(MarketplaceConnection $connection): void
    {
        $client = $this->marketplaceClient->make($connection->marketplace, $connection->credentials);

        $stocks = $client->getStocks();

        if ($stocks->isEmpty()) {
            Log::warning(sprintf('SyncStockFromMarketplaceAction: No stocks returned from %s for connection ID: %d', $connection->marketplace->value, $connection->id));

            return;
        }

        // Delegate saving logic to the dedicated action
        $this->saveStockAction->execute($connection, $stocks);
    }
}
