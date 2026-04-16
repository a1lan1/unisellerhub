<?php

declare(strict_types=1);

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class OrganizationIdObserver
{
    public function creating(Model $model): void
    {
        if (Auth::check() && ! $model->getAttribute('organization_id')) {
            $model->setAttribute('organization_id', Auth::user()->organization_id);
        }
    }
}
