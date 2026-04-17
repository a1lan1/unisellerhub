<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Application\Actions\Webhooks;

use App\Modules\Inventory\Infrastructure\Jobs\SyncMoySkladStockJob;
use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use App\Modules\Marketplace\Domain\Repositories\MarketplaceConnectionRepositoryInterface;
use App\Modules\User\Domain\Interfaces\NotificationServiceInterface;
use Illuminate\Support\Facades\Log;

readonly class ProcessMoySkladWebhookAction
{
    public function __construct(
        private MarketplaceConnectionRepositoryInterface $marketplaceConnectionRepository,
        private NotificationServiceInterface $notificationService,
    ) {}

    public function execute(string $token, array $events): void
    {
        $msToken = str_replace('Bearer ', '', $token);
        Log::info('MoySklad Webhook received, processing...', ['token' => $msToken]);

        $connection = $this->marketplaceConnectionRepository->findByMarketplaceAndCredentials(
            MarketplaceEnum::MOYSKLAD,
            'ms_token',
            $msToken
        );

        if ($connection instanceof MarketplaceConnection) {
            $organization = $connection->organization;
            $triggered = false;
            foreach ($events as $event) {
                $type = (string) ($event['meta']['type'] ?? '');
                if (str_contains($type, 'product') || str_contains($type, 'stock')) {
                    dispatch(new SyncMoySkladStockJob($organization->id));
                    $triggered = true;
                }
            }

            if ($triggered) {
                $this->notificationService->sendToOrganization(
                    $organization,
                    'Stock updated in MoySklad. Syncing to marketplaces...'
                );
            }
        }
    }
}
