<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\Enums;

enum QueueNameEnum: string
{
    case Default = 'default';
    case SyncTasks = 'sync.tasks';
    case SyncResults = 'sync.results';
    case ReportTasks = 'report.tasks';
    case ReportResults = 'report.results';
    case NotificationsTelegram = 'notifications.telegram';
    case PriceTasks = 'price.tasks';
    case GeoReviews = 'geo_reviews';
    case MeilisearchTasks = 'meilisearch.tasks';
    case HighPriority = 'high_priority';
    case LowPriority = 'low_priority';
}
