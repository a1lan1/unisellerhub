<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\Policies;

use App\Modules\Geo\Domain\Models\ResponseTemplate;
use App\Modules\User\Domain\Models\User;

class ResponseTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ResponseTemplate $template): bool
    {
        return $user->id === $template->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ResponseTemplate $template): bool
    {
        return $user->id === $template->user_id;
    }

    public function delete(User $user, ResponseTemplate $template): bool
    {
        return $user->id === $template->user_id;
    }
}
