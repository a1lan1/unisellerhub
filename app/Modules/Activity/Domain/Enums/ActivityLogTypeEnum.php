<?php

declare(strict_types=1);

namespace App\Modules\Activity\Domain\Enums;

enum ActivityLogTypeEnum: string
{
    case SyncStart = 'sync_start';
    case SyncDispatched = 'sync_dispatched';
    case SyncError = 'sync_error';
}
