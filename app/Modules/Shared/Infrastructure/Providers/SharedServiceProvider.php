<?php

declare(strict_types=1);

namespace App\Modules\Shared\Infrastructure\Providers;

use App\Modules\Shared\Application\Services\TenantManager;
use Illuminate\Support\ServiceProvider;
use Override;

class SharedServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->singleton(TenantManager::class);
    }

    public function boot(): void
    {
        //
    }
}
