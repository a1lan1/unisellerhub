<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\Enums;

enum CacheKeyEnum: string
{
    case USER_PERMISSIONS = 'user_permissions_%d';
    case USER_ROLES = 'user_roles_%d';
}
