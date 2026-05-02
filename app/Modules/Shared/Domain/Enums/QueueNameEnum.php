<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\Enums;

enum QueueNameEnum: string
{
    case Default = 'default';
    case SyncResults = 'sync.results';
    case SyncTasks = 'sync.tasks';
    case ReportTasks = 'report.tasks';
    case NotificationsTelegram = 'notifications.telegram';
    case MeilisearchTasks = 'meilisearch.tasks';
    case HighPriority = 'high_priority';
    case LowPriority = 'low_priority';
}
