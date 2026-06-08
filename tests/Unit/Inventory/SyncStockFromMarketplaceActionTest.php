<?php

declare(strict_types=1);

use App\Modules\Inventory\Application\Actions\SaveMarketplaceStockAction;
use App\Modules\Inventory\Domain\Actions\SyncStockFromMarketplaceAction;
use App\Modules\Inventory\Domain\Data\StockData;
use App\Modules\Inventory\Domain\ValueObjects\ExternalProductId;
use App\Modules\Inventory\Domain\ValueObjects\ExternalWarehouseId;
use App\Modules\Inventory\Domain\ValueObjects\Quantity;
use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Marketplace\Domain\Interfaces\MarketplaceClientInterface;
use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use App\Modules\Marketplace\Infrastructure\Factories\MarketplaceClientFactory;
use App\Modules\Product\ValueObjects\Sku;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

beforeEach(function (): void {
    Log::swap($this->mock(LoggerInterface::class));

    $this->saveStockAction = $this->mock(SaveMarketplaceStockAction::class)->makePartial();
    $this->marketplaceClientFactory = $this->mock(MarketplaceClientFactory::class)->makePartial();
    $this->action = new SyncStockFromMarketplaceAction(
        $this->saveStockAction,
        $this->marketplaceClientFactory
    );

    $this->connection = MarketplaceConnection::factory()->make([
        'marketplace' => MarketplaceEnum::OZON,
        'credentials' => ['token' => 'test_token'],
    ]);
    $this->marketplaceClient = $this->mock(MarketplaceClientInterface::class);
});

it('syncs stocks from marketplace and saves them', function (): void {
    $stocks = new Collection([
        new StockData(
            external_product_id: new ExternalProductId('ext-1'),
            external_warehouse_id: new ExternalWarehouseId('wh-1'),
            quantity: new Quantity(10),
            sku: new Sku('SKU001')
        ),
    ]);

    $this->marketplaceClientFactory->shouldReceive('make')
        ->once()
        ->with($this->connection->marketplace, $this->connection->credentials)
        ->andReturn($this->marketplaceClient);

    $this->marketplaceClient->shouldReceive('getStocks')
        ->once()
        ->andReturn($stocks);

    $this->saveStockAction->shouldReceive('execute')
        ->once()
        ->with($this->connection, $stocks);

    $this->action->execute($this->connection);
});

it('logs warning and does not save if no stocks are returned', function (): void {
    $stocks = new Collection;

    $this->marketplaceClientFactory->shouldReceive('make')
        ->once()
        ->with($this->connection->marketplace, $this->connection->credentials)
        ->andReturn($this->marketplaceClient);

    $this->marketplaceClient->shouldReceive('getStocks')
        ->once()
        ->andReturn($stocks);

    Log::shouldReceive('warning')
        ->once()
        ->with(sprintf('SyncStockFromMarketplaceAction: No stocks returned from %s for connection ID: %d', $this->connection->marketplace->value, $this->connection->id));

    $this->saveStockAction->shouldNotReceive('execute');

    $this->action->execute($this->connection);
});
