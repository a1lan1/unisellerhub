<?php

declare(strict_types=1);

namespace Tests\Feature\MockMarketplace;

use App\Modules\MockMarketplace\Domain\Models\MockMarketplaceAccount;
use App\Modules\MockMarketplace\Domain\Models\MockMarketplaceCredential;
use App\Modules\MockMarketplace\Domain\Models\MockOrder;
use App\Modules\MockMarketplace\Domain\Models\MockProduct;
use App\Modules\MockMarketplace\Domain\Models\MockStock;
use App\Modules\MockMarketplace\Domain\Models\MockWarehouse;
use App\Modules\Order\Domain\Enums\OrderStatusEnum;

test('wildberries stocks mock returns correct format', function (): void {
    $account = MockMarketplaceAccount::factory()->create(['marketplace' => 'wb']);
    $token = 'test-wb-token';
    MockMarketplaceCredential::create([
        'mock_marketplace_account_id' => $account->id,
        'key' => 'token',
        'value' => $token,
    ]);

    $warehouse = MockWarehouse::factory()->create([
        'marketplace' => 'wb',
        'mock_marketplace_account_id' => $account->id,
        'external_id' => '123456',
    ]);
    $product = MockProduct::factory()->create(['marketplace' => 'wb', 'mock_marketplace_account_id' => $account->id]);
    MockStock::factory()->create([
        'marketplace' => 'wb',
        'mock_marketplace_account_id' => $account->id,
        'external_product_id' => $product->external_id,
        'external_warehouse_id' => $warehouse->external_id,
        'sku' => $product->vendor_code,
        'quantity' => 100,
    ]);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/mock/wb/api/v3/stocks?warehouseId='.(int) $warehouse->external_id);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'sku' => $product->vendor_code,
            'amount' => 100,
            'warehouseId' => (int) $warehouse->external_id,
        ]);
});

test('wildberries orders mock returns correct format', function (): void {
    $account = MockMarketplaceAccount::factory()->create(['marketplace' => 'wb']);
    $token = 'test-wb-token';
    MockMarketplaceCredential::create([
        'mock_marketplace_account_id' => $account->id,
        'key' => 'token',
        'value' => $token,
    ]);

    MockOrder::factory()->create([
        'marketplace' => 'wb',
        'mock_marketplace_account_id' => $account->id,
        'status' => OrderStatusEnum::PENDING,
    ]);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/mock/wb/api/v3/orders');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'orders' => [
                '*' => ['id', 'createdAt', 'price', 'items'],
            ],
        ]);
});

test('ozon product list mock returns correct format', function (): void {
    $account = MockMarketplaceAccount::factory()->create(['marketplace' => 'ozon']);
    $clientId = 'test-ozon-client-id';
    MockMarketplaceCredential::create([
        'mock_marketplace_account_id' => $account->id,
        'key' => 'client_id',
        'value' => $clientId,
    ]);

    MockProduct::factory()->count(3)->create(['marketplace' => 'ozon', 'mock_marketplace_account_id' => $account->id]);

    $response = $this->withHeader('Client-Id', $clientId)
        ->postJson('/api/mock/ozon/v1/product/list', [
            'limit' => 10,
            'offset' => 0,
        ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'result' => [
                'items' => [
                    '*' => ['product_id', 'offer_id'],
                ],
                'total',
            ],
        ]);
});

test('ozon stocks mock returns correct format', function (): void {
    $account = MockMarketplaceAccount::factory()->create(['marketplace' => 'ozon']);
    $clientId = 'test-ozon-client-id';
    MockMarketplaceCredential::create([
        'mock_marketplace_account_id' => $account->id,
        'key' => 'client_id',
        'value' => $clientId,
    ]);

    $product = MockProduct::factory()->create(['marketplace' => 'ozon', 'mock_marketplace_account_id' => $account->id]);
    $warehouse = MockWarehouse::factory()->create(['marketplace' => 'ozon', 'mock_marketplace_account_id' => $account->id]);
    MockStock::factory()->create([
        'marketplace' => 'ozon',
        'mock_marketplace_account_id' => $account->id,
        'external_product_id' => $product->external_id,
        'external_warehouse_id' => $warehouse->external_id,
        'sku' => $product->vendor_code,
        'quantity' => 50,
    ]);

    $response = $this->withHeader('Client-Id', $clientId)
        ->postJson('/api/mock/ozon/v1/product/info/stocks', [
            'product_id' => [(int) $product->external_id],
        ]);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'product_id' => (int) $product->external_id,
            'offer_id' => $product->vendor_code,
        ]);
});
