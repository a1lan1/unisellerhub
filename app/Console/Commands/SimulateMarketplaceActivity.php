<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\MockMarketplace\Domain\Models\MockMarketplaceAccount;
use App\Modules\MockMarketplace\Domain\Models\MockOrder;
use App\Modules\MockMarketplace\Domain\Models\MockProduct;
use App\Modules\MockMarketplace\Domain\Models\MockStock;
use App\Modules\MockMarketplace\Domain\Models\MockWarehouse;
use App\Modules\Order\Domain\Enums\OrderStatusEnum;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Random\RandomException;
use Throwable;

class SimulateMarketplaceActivity extends Command
{
    protected $signature = 'mock:simulate-activity';

    protected $description = 'Simulate external marketplace activity using model factories with stock management and webhooks';

    /**
     * @throws RandomException
     * @throws Throwable
     * @throws ConnectionException
     */
    public function handle(): void
    {
        if (! $account = MockMarketplaceAccount::inRandomOrder()->first()) {
            $this->warn('No mock accounts found.');

            return;
        }

        // 5% chance to create a new product instead of activity
        if (random_int(1, 100) <= 5) {
            $this->createNewMockProduct($account);

            return;
        }

        $this->info('Simulating activity for: '.$account->marketplace->label());
        logger('Simulating activity for: '.$account->marketplace->label());

        switch ($account->marketplace) {
            case MarketplaceEnum::WB:
                $this->simulateWbActivity($account);
                break;
            case MarketplaceEnum::OZON:
                $this->simulateOzonActivity($account);
                break;
            case MarketplaceEnum::MOYSKLAD:
                $this->simulateMsActivity($account);
                break;
            case MarketplaceEnum::YANDEX:
                $this->simulateYandexActivity($account);
                break;
            case MarketplaceEnum::AVITO:
                $this->simulateAvitoActivity($account);
                break;
        }
    }

    /**
     * @throws RandomException
     * @throws ConnectionException
     * @throws RequestException
     */
    private function createNewMockProduct(MockMarketplaceAccount $account): void
    {
        $marketplace = $account->marketplace;

        // Use factory for product creation
        $productFactory = MockProduct::factory();
        if ($marketplace === MarketplaceEnum::MOYSKLAD) {
            $productFactory = $productFactory->moysklad();
        } else {
            $productFactory = $productFactory->state(['marketplace' => $marketplace]);
        }

        /** @var MockProduct $product */
        $product = $productFactory->create(['mock_marketplace_account_id' => $account->id]);

        // Find or create a warehouse for the account using factory
        /** @var MockWarehouse $warehouse */
        $warehouse = MockWarehouse::where('mock_marketplace_account_id', $account->id)->first()
            ?? MockWarehouse::factory()->create([
                'mock_marketplace_account_id' => $account->id,
                'marketplace' => $marketplace,
            ]);

        // Create initial stock using factory
        MockStock::factory()->create([
            'mock_marketplace_account_id' => $account->id,
            'marketplace' => $marketplace,
            'external_product_id' => $product->external_id,
            'external_warehouse_id' => $warehouse->external_id,
            'sku' => $product->vendor_code,
            'quantity' => random_int(10, 50),
        ]);

        $this->info(sprintf('Created new product %s on %s using Factory.', $product->vendor_code, $marketplace->label()));

        // Mocking event that happens on MP when product is added
        if ($marketplace === MarketplaceEnum::MOYSKLAD) {
            $token = $account->credentials()->where('key', 'ms_token')->value('value');

            rescue(function () use ($token): void {
                $response = Http::timeout(5)
                    ->withHeader('Authorization', 'Bearer '.$token)
                    ->post(
                        $this->internalUrl(route('webhooks.ms')),
                        ['events' => [['meta' => ['type' => 'product']]]]
                    );

                $response->throw();
            });
        }
    }

    /**
     * @throws Throwable
     */
    private function createOrderWithStockUpdate(MockMarketplaceAccount $account): ?string
    {
        return DB::transaction(function () use ($account) {
            $marketplace = $account->marketplace;

            // Find stock with quantity > 0
            $stock = MockStock::where('mock_marketplace_account_id', $account->id)
                ->where('quantity', '>', 0)
                ->inRandomOrder()
                ->first();

            if (! $stock) {
                return null;
            }

            // Create Mock Order using Factory
            /** @var MockOrder $order */
            $order = MockOrder::factory()
                ->marketplace($marketplace)
                ->create([
                    'mock_marketplace_account_id' => $account->id,
                    'status' => OrderStatusEnum::PENDING,
                    'items' => [
                        [
                            'product_id' => $stock->external_product_id,
                            'sku' => $stock->sku,
                            'quantity' => 1,
                            'price' => random_int(1000, 5000),
                        ],
                    ],
                ]);

            // Reduce Stock in Mock Layer
            $stock->decrement('quantity');

            $this->info(sprintf('Order %s created for %s. Stock reduced to %s.', $order->external_order_id, $stock->sku, $stock->quantity));

            return $order->external_order_id;
        });
    }

    /**
     * @throws ConnectionException
     * @throws Throwable
     */
    private function simulateWbActivity(MockMarketplaceAccount $account): void
    {
        $orderId = $this->createOrderWithStockUpdate($account);
        if (! $orderId) {
            return;
        }

        $token = $account->credentials()->where('key', 'token')->value('value');

        rescue(function () use ($token, $orderId): void {
            $response = Http::timeout(5)
                ->withHeader('Authorization', $token)
                ->post(
                    $this->internalUrl(route('webhooks.wb')),
                    ['order_id' => $orderId]
                );

            $response->throw();
        });
    }

    /**
     * @throws Throwable
     * @throws ConnectionException
     */
    private function simulateOzonActivity(MockMarketplaceAccount $account): void
    {
        $orderId = $this->createOrderWithStockUpdate($account);
        if (! $orderId) {
            return;
        }

        $clientId = $account->credentials()->where('key', 'client_id')->value('value');

        rescue(function () use ($clientId, $orderId): void {
            $response = Http::timeout(5)
                ->withHeader('Client-Id', $clientId)
                ->post(
                    $this->internalUrl(route('webhooks.ozon')),
                    ['message_type' => 'TYPE_NEW_POSTING', 'posting_number' => $orderId]
                );

            $response->throw();
        });
    }

    /**
     * @throws RandomException
     * @throws ConnectionException
     * @throws RequestException
     */
    private function simulateMsActivity(MockMarketplaceAccount $account): void
    {
        // For MoySklad, activity usually means someone changed stock manually or received delivery
        $stock = MockStock::where('mock_marketplace_account_id', $account->id)->inRandomOrder()->first();
        if ($stock) {
            $stock->increment('quantity', random_int(5, 15));
            $this->info(sprintf('Restocked %s on MoySklad. New stock: %s', $stock->sku, $stock->quantity));
        }

        $token = $account->credentials()->where('key', 'ms_token')->value('value');

        rescue(function () use ($token): void {
            $response = Http::timeout(5)
                ->withHeader('Authorization', 'Bearer '.$token)
                ->post(
                    $this->internalUrl(route('webhooks.ms')),
                    ['events' => [['meta' => ['type' => 'stock']]]]
                );

            $response->throw();
        });
    }

    /**
     * @throws Throwable
     * @throws ConnectionException
     */
    private function simulateYandexActivity(MockMarketplaceAccount $account): void
    {
        $orderId = $this->createOrderWithStockUpdate($account);
        if (! $orderId) {
            return;
        }

        $apiKey = $account->credentials()->where('key', 'api_key')->value('value');

        rescue(function () use ($apiKey, $orderId): void {
            $response = Http::timeout(5)
                ->withHeader('Api-Key', $apiKey)
                ->post(
                    $this->internalUrl(route('webhooks.yandex')),
                    ['order_id' => $orderId]
                );

            $response->throw();
        });
    }

    /**
     * @throws Throwable
     * @throws RandomException
     * @throws ConnectionException
     */
    private function simulateAvitoActivity(MockMarketplaceAccount $account): void
    {
        // Avito orders are special, let's just simulate a message or order
        $isOrder = random_int(0, 1) === 1;
        $clientId = $account->credentials()->where('key', 'client_id')->value('value');

        if ($isOrder) {
            $orderId = $this->createOrderWithStockUpdate($account);
            if (! $orderId) {
                return;
            }

            $event = 'order.new';
        } else {
            $event = 'message.new';
        }

        rescue(function () use ($clientId, $event): void {
            $response = Http::timeout(5)
                ->withToken($clientId)
                ->post(
                    $this->internalUrl(route('webhooks.avito')),
                    [
                        'event_name' => $event,
                        'payload' => ['chat_id' => 'chat_'.Str::random(5)],
                    ]
                );

            $response->throw();
        });
    }

    private function internalUrl(string $route): string
    {
        return str_replace('localhost', 'app', $route);
    }
}
