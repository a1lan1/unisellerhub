<?php

declare(strict_types=1);

namespace App\Modules\Product\Domain\Actions;

use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use App\Modules\Marketplace\Infrastructure\Factories\MarketplaceClientFactory;
use App\Modules\Product\Application\Actions\SaveMarketplaceProductsAction;

final readonly class SyncProductsFromMarketplaceAction
{
    public function __construct(
        private SaveMarketplaceProductsAction $saveProductsAction,
        private MarketplaceClientFactory $marketplaceClient
    ) {}

    /**
     * Syncs products from a specific marketplace connection to our local database.
     */
    public function execute(MarketplaceConnection $connection): void
    {
        $client = $this->marketplaceClient->make($connection->marketplace, $connection->credentials);

        // 1. Fetch products from marketplace API
        $products = $client->getProducts();

        // 2. Delegate saving to a dedicated action
        $this->saveProductsAction->execute($connection, $products);
    }
}
