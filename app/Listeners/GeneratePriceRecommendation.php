<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Ai\Agents\PricingStrategyCoach;
use App\Modules\Product\Domain\Events\PriceAnalysisCompleted;
use App\Modules\User\Domain\Data\NotificationData;
use App\Modules\User\Domain\Enums\NotificationTypeEnum;
use App\Modules\User\Domain\Interfaces\NotificationServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

final readonly class GeneratePriceRecommendation implements ShouldQueue
{
    public function __construct(private NotificationServiceInterface $notificationService) {}

    public function handle(PriceAnalysisCompleted $event): void
    {
        try {
            $stats = $event->stats;

            $prompt = sprintf(
                'Marketplace: %s. Stats: Daily Sales: %.2f, Days Left: %d, Trend: %s, Current Stock: %d. Provide a price recommendation.',
                $event->marketplace,
                $stats['avg_daily_sales'],
                $stats['days_left'],
                $stats['trend'],
                $stats['current_stock']
            );

            // 1. Generate recommendation via AI Agent
            $recommendation = (string) PricingStrategyCoach::make()->prompt($prompt);

            // 2. Send Notification via Service
            $this->notificationService->sendToOrganizationById(
                $event->organizationId,
                new NotificationData(
                    title: 'Price Recommendation',
                    message: $recommendation,
                    type: NotificationTypeEnum::INFO,
                    icon: 'mdi-currency-usd'
                )
            );

            Log::info(sprintf('Price Recommendation notified for Product %s (Org: %d)', $event->productId, $event->organizationId));
        } catch (Throwable $throwable) {
            Log::error('Failed to generate price recommendation: '.$throwable->getMessage());
        }
    }
}
