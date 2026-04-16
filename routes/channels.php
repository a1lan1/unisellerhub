<?php

declare(strict_types=1);

use App\Modules\User\Domain\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', fn (User $user, int $id): bool => $user->id === $id);
Broadcast::channel('organization.{id}', fn (User $user, int $id): bool => $user->organization_id === $id);
