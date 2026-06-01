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

class ProcessOzonWebhookAction
{
    public function __construct(
        private readonly MarketplaceConnectionRepositoryInterface $marketplaceConnectionRepository,
        private readonly NotificationServiceInterface $notificationService,
    ) {}

    public function execute(string $clientId, ?string $messageType): void
    {
        Log::info('Ozon Webhook received, processing...', ['clientId' => $clientId, 'messageType' => $messageType]);

        $connection = $this->marketplaceConnectionRepository->findByMarketplaceAndCredentials(
            MarketplaceEnum::OZON,
            'client_id',
            $clientId
        );

        if ($connection instanceof MarketplaceConnection) {
            $organization = $connection->organization;
            if ($messageType === 'TYPE_NEW_POSTING') {
                dispatch(new SyncOrdersJob($organization->id, MarketplaceEnum::OZON));

                $this->notificationService->sendToOrganization(
                    $organization,
                    new NotificationData(
                        title: 'New Order',
                        message: sprintf('New order received from Ozon (%s). Syncing...', $connection->name),
                        type: NotificationTypeEnum::SUCCESS,
                        icon: 'mdi-cart'
                    )
                );
            }
        }
    }
}
