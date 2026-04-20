<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Application\Actions\Webhooks;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use App\Modules\Marketplace\Domain\Repositories\MarketplaceConnectionRepositoryInterface;
use App\Modules\Order\Infrastructure\Jobs\SyncOrdersJob;
use App\Modules\User\Domain\Data\NotificationData;
use App\Modules\User\Domain\Enums\NotificationTypeEnum;
use App\Modules\User\Domain\Interfaces\NotificationServiceInterface;
use Illuminate\Support\Facades\Log;

readonly class ProcessAvitoWebhookAction
{
    public function __construct(
        private MarketplaceConnectionRepositoryInterface $marketplaceConnectionRepository,
        private NotificationServiceInterface $notificationService,
    ) {}

    public function execute(string $token, string $eventName): void
    {
        $avitoToken = str_replace('Bearer ', '', $token);

        Log::info('Avito Webhook received, processing...', ['token' => $avitoToken, 'eventName' => $eventName]);

        $connection = $this->marketplaceConnectionRepository->findByMarketplaceAndCredentials(
            MarketplaceEnum::AVITO,
            'client_id', // Simplified for mock: client_id is used as token
            $avitoToken
        );

        if ($connection instanceof MarketplaceConnection) {
            $organization = $connection->organization;
            if ($eventName === 'message.new' || $eventName === 'chat.new') {
                $this->notificationService->sendToOrganization(
                    $organization,
                    new NotificationData(
                        title: 'New Message',
                        message: sprintf('New customer message on Avito (%s)', $connection->name),
                        type: NotificationTypeEnum::INFO,
                        icon: 'mdi-chat'
                    )
                );
            } elseif ($eventName === 'order.new') {
                dispatch(new SyncOrdersJob($connection->organization_id, MarketplaceEnum::AVITO));

                $this->notificationService->sendToOrganization(
                    $organization,
                    new NotificationData(
                        title: 'New Order',
                        message: sprintf('New order received from Avito (%s). Syncing...', $connection->name),
                        type: NotificationTypeEnum::SUCCESS,
                        icon: 'mdi-cart'
                    )
                );
            }
        }
    }
}
