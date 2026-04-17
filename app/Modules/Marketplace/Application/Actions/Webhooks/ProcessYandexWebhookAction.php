<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Application\Actions\Webhooks;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use App\Modules\Marketplace\Domain\Repositories\MarketplaceConnectionRepositoryInterface;
use App\Modules\Order\Infrastructure\Jobs\SyncOrdersJob;
use App\Modules\User\Domain\Interfaces\NotificationServiceInterface;
use Illuminate\Support\Facades\Log;

readonly class ProcessYandexWebhookAction
{
    public function __construct(
        private MarketplaceConnectionRepositoryInterface $marketplaceConnectionRepository,
        private NotificationServiceInterface $notificationService,
    ) {}

    public function execute(string $apiKey, string $orderId): void
    {
        Log::info('Yandex Webhook received, processing...', ['orderId' => $orderId]);

        $connection = $this->marketplaceConnectionRepository->findByMarketplaceAndCredentials(
            MarketplaceEnum::YANDEX,
            'api_key',
            $apiKey
        );

        if ($connection instanceof MarketplaceConnection) {
            $organization = $connection->organization;
            dispatch(new SyncOrdersJob($organization->id, MarketplaceEnum::YANDEX));

            $this->notificationService->sendToOrganization(
                $organization,
                sprintf('New order received from Yandex Market (%s). Order ID: %s', $connection->name, $orderId)
            );
        }
    }
}
