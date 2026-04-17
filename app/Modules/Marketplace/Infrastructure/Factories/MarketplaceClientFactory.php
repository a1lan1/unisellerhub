<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Infrastructure\Factories;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Marketplace\Domain\Interfaces\MarketplaceClientInterface;
use App\Modules\Marketplace\Infrastructure\Clients\AvitoClientAdapter;
use App\Modules\Marketplace\Infrastructure\Clients\MoySkladClientAdapter;
use App\Modules\Marketplace\Infrastructure\Clients\OzonClientAdapter;
use App\Modules\Marketplace\Infrastructure\Clients\WbClientAdapter;
use App\Modules\Marketplace\Infrastructure\Clients\YandexClientAdapter;

final readonly class MarketplaceClientFactory
{
    /**
     * Create a marketplace client instance.
     */
    public function make(MarketplaceEnum $marketplace, array $credentials): MarketplaceClientInterface
    {
        return match ($marketplace) {
            MarketplaceEnum::WB => new WbClientAdapter(
                baseUrl: config('marketplace.wildberries.base_url'),
                token: $credentials['token'] ?? '',
                timeout: (int) config('marketplace.wildberries.timeout', 30)
            ),
            MarketplaceEnum::OZON => new OzonClientAdapter(
                baseUrl: config('marketplace.ozon.base_url'),
                headers: [
                    'Client-Id' => $credentials['client_id'] ?? '',
                    'Api-Key' => $credentials['api_key'] ?? '',
                ],
                timeout: (int) config('marketplace.ozon.timeout', 30)
            ),
            MarketplaceEnum::YANDEX => new YandexClientAdapter(
                baseUrl: config('marketplace.yandex.base_url'),
                apiKey: $credentials['api_key'] ?? '',
                campaignId: $credentials['campaign_id'] ?? null,
                businessId: $credentials['business_id'] ?? null,
                timeout: (int) config('marketplace.yandex.timeout', 30)
            ),
            MarketplaceEnum::MOYSKLAD => new MoySkladClientAdapter(
                baseUrl: config('marketplace.moysklad.base_url'),
                token: $credentials['ms_token'] ?? '',
                timeout: (int) config('marketplace.moysklad.timeout', 30)
            ),
            MarketplaceEnum::AVITO => new AvitoClientAdapter(
                baseUrl: config('marketplace.avito.base_url'),
                clientId: $credentials['client_id'] ?? '',
                clientSecret: $credentials['client_secret'] ?? '',
                timeout: (int) config('marketplace.avito.timeout', 30)
            ),
        };
    }
}
