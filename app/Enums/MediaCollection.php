<?php

declare(strict_types=1);

namespace App\Enums;

enum MediaCollection: string
{
    case UserAvatar = 'user.avatar';

    case UserAvatarThumb = 'user.avatar-thumb';
}
