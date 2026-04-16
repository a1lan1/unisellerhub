<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\MockMarketplace\Domain\Models\MockMarketplaceAccount;
use App\Modules\MockMarketplace\Domain\Models\MockOrder;
use App\Modules\MockMarketplace\Domain\Models\MockProduct;
use App\Modules\MockMarketplace\Domain\Models\MockStock;
use App\Modules\MockMarketplace\Domain\Models\MockWarehouse;
use App\Modules\User\Domain\Enums\RoleEnum;
use App\Modules\User\Domain\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Random\RandomException;

class MockMarketplaceSeeder extends Seeder
{
    /**
     * @throws RandomException
     */
    public function run(): void
    {
        $marketplaces = [
            MarketplaceEnum::WB,
            MarketplaceEnum::OZON,
            MarketplaceEnum::MOYSKLAD,
            MarketplaceEnum::YANDEX,
            MarketplaceEnum::AVITO,
        ];

        $seller = User::role(RoleEnum::SELLER)->firstOrFail();

        foreach ($marketplaces as $marketplace) {
            $mockAccount = MockMarketplaceAccount::create([
                'marketplace' => $marketplace,
                'name' => 'Mock '.$marketplace->label().' for '.$seller->name,
            ]);

            $credentials = $this->createAccountCredentials($marketplace, $mockAccount);

            $seller->organization->marketplaceConnections()->create([
                'marketplace' => $marketplace,
                'name' => 'My '.$marketplace->label().' Account',
                'credentials' => $credentials,
                'is_active' => true,
            ]);

            MockWarehouse::factory()
                ->create([
                    'mock_marketplace_account_id' => $mockAccount->id,
                    'marketplace' => $marketplace,
                    'external_id' => $marketplace === MarketplaceEnum::YANDEX ? 'yandex_wh_1' : fake()->uuid(),
                ])
                ->each(function (MockWarehouse $warehouse) use ($mockAccount, $marketplace): void {
                    $factory = MockProduct::factory(random_int(5, 10));

                    if ($marketplace === MarketplaceEnum::MOYSKLAD) {
                        $factory = $factory->moysklad();
                    } else {
                        $factory = $factory->state(['marketplace' => $marketplace]);
                    }

                    $factory->create(['mock_marketplace_account_id' => $mockAccount->id])
                        ->each(function (MockProduct $product) use ($mockAccount, $marketplace, $warehouse): void {
                            MockStock::factory()->create([
                                'mock_marketplace_account_id' => $mockAccount->id,
                                'marketplace' => $marketplace,
                                'external_product_id' => $product->external_id,
                                'external_warehouse_id' => $warehouse->external_id,
                                'sku' => $product->vendor_code,
                            ]);
                        });

                    MockOrder::factory()
                        ->count(random_int(300, 500))
                        ->marketplace($marketplace)
                        ->create(['mock_marketplace_account_id' => $mockAccount->id]);
                });
        }
    }

    /**
     * @throws RandomException
     */
    private function createAccountCredentials(MarketplaceEnum $marketplace, MockMarketplaceAccount &$mockAccount): array
    {
        $credentials = [];

        if ($marketplace === MarketplaceEnum::WB) {
            $token = 'wb_token_'.Str::random(20);
            $credentials = ['token' => $token];
            $mockAccount->credentials()->create(['key' => 'token', 'value' => $token]);
        } elseif ($marketplace === MarketplaceEnum::OZON) {
            $clientId = 'ozon_client_'.Str::random(8);
            $apiKey = 'ozon_key_'.Str::random(12);
            $credentials = ['client_id' => $clientId, 'api_key' => $apiKey];
            $mockAccount->credentials()->create(['key' => 'client_id', 'value' => $clientId]);
            $mockAccount->credentials()->create(['key' => 'api_key', 'value' => $apiKey]);
        } elseif ($marketplace === MarketplaceEnum::MOYSKLAD) {
            $msToken = 'ms_token_'.Str::random(30);
            $credentials = ['ms_token' => $msToken];
            $mockAccount->credentials()->create(['key' => 'ms_token', 'value' => $msToken]);
        } elseif ($marketplace === MarketplaceEnum::YANDEX) {
            $apiKey = 'yandex_key_'.Str::random(15);
            $campaignId = (string) random_int(100000, 999999);
            $businessId = (string) random_int(100000, 999999);
            $credentials = ['api_key' => $apiKey, 'campaign_id' => $campaignId, 'business_id' => $businessId];
            $mockAccount->credentials()->create(['key' => 'api_key', 'value' => $apiKey]);
            $mockAccount->credentials()->create(['key' => 'campaign_id', 'value' => $campaignId]);
            $mockAccount->credentials()->create(['key' => 'business_id', 'value' => $businessId]);
        } elseif ($marketplace === MarketplaceEnum::AVITO) {
            $clientId = 'avito_client_'.Str::random(10);
            $clientSecret = 'avito_secret_'.Str::random(20);
            $credentials = ['client_id' => $clientId, 'client_secret' => $clientSecret];
            $mockAccount->credentials()->create(['key' => 'client_id', 'value' => $clientId]);
            $mockAccount->credentials()->create(['key' => 'client_secret', 'value' => $clientSecret]);
        } else {
            throw new InvalidArgumentException('Price cannot be negative.');
        }

        return $credentials;
    }
}
