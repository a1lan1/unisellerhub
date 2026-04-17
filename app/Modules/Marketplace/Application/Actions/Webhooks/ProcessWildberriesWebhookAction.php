<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Application\Actions\Webhooks;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use App\Modules\Marketplace\Domain\Repositories\MarketplaceConnectionRepositoryInterface;
use App\Modules\Order\Infrastructure\Jobs\SyncOrdersJob;
use App\Modules\User\Domain\Interfaces\NotificationServiceInterface;
use Illuminate\Support\Facades\Log;

readonly class ProcessWildberriesWebhookAction
{
    public function __construct(
        private MarketplaceConnectionRepositoryInterface $marketplaceConnectionRepository,
        private NotificationServiceInterface $notificationService,
    ) {}

    public function execute(string $token): void
    {
        Log::info('WB Webhook received, processing...', ['token' => $token]);

        $connection = $this->marketplaceConnectionRepository->findByMarketplaceAndCredentials(
            MarketplaceEnum::WB,
            'token',
            $token
        );

        if ($connection instanceof MarketplaceConnection) {
            $organization = $connection->organization;
            dispatch(new SyncOrdersJob($organization->id, MarketplaceEnum::WB));

            $this->notificationService->sendToOrganization(
                $organization,
                sprintf('New order received from Wildberries (%s)', $connection->name)
            );
        }
    }
}
