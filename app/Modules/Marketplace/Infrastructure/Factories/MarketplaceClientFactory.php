<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Infrastructure\Factories;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Marketplace\Domain\Interfaces\MarketplaceClientInterface;
use App\Modules\Marketplace\Domain\ValueObjects\Credentials;
use App\Modules\Marketplace\Infrastructure\Clients\AvitoClientAdapter;
use App\Modules\Marketplace\Infrastructure\Clients\MoySkladClientAdapter;
use App\Modules\Marketplace\Infrastructure\Clients\OzonClientAdapter;
use App\Modules\Marketplace\Infrastructure\Clients\WbClientAdapter;
use App\Modules\Marketplace\Infrastructure\Clients\YandexClientAdapter;

class MarketplaceClientFactory
{
    /**
     * Create a marketplace client instance.
     */
    public function make(MarketplaceEnum $marketplace, Credentials $credentials): MarketplaceClientInterface
    {
        $creds = $credentials->getValue();

        return match ($marketplace) {
            MarketplaceEnum::WB => new WbClientAdapter(
                baseUrl: config('marketplace.wildberries.base_url'),
                token: $creds['token'] ?? '',
                timeout: (int) config('marketplace.wildberries.timeout', 30)
            ),
            MarketplaceEnum::OZON => new OzonClientAdapter(
                baseUrl: config('marketplace.ozon.base_url'),
                headers: [
                    'Client-Id' => $creds['client_id'] ?? '',
                    'Api-Key' => $creds['api_key'] ?? '',
                ],
                timeout: (int) config('marketplace.ozon.timeout', 30)
            ),
            MarketplaceEnum::YANDEX => new YandexClientAdapter(
                baseUrl: config('marketplace.yandex.base_url'),
                apiKey: $creds['api_key'] ?? '',
                campaignId: $creds['campaign_id'] ?? null,
                businessId: $creds['business_id'] ?? null,
                timeout: (int) config('marketplace.yandex.timeout', 30)
            ),
            MarketplaceEnum::MOYSKLAD => new MoySkladClientAdapter(
                baseUrl: config('marketplace.moysklad.base_url'),
                token: $creds['ms_token'] ?? '',
                timeout: (int) config('marketplace.moysklad.timeout', 30)
            ),
            MarketplaceEnum::AVITO => new AvitoClientAdapter(
                baseUrl: config('marketplace.avito.base_url'),
                clientId: $creds['client_id'] ?? '',
                clientSecret: $creds['client_secret'] ?? '',
                timeout: (int) config('marketplace.avito.timeout', 30)
            ),
        };
    }
}
