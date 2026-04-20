<?php

declare(strict_types=1);

namespace App\Modules\User\Infrastructure\Providers;

use App\Modules\User\Application\Services\NotificationService;
use App\Modules\User\Domain\Interfaces\NotificationRepositoryInterface;
use App\Modules\User\Domain\Interfaces\NotificationServiceInterface;
use App\Modules\User\Domain\Policies\OrganizationPolicy;
use App\Modules\User\Infrastructure\Repositories\EloquentNotificationRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Override;

class UserServiceProvider extends ServiceProvider
{
    /**
     * @var array<string, string>
     */
    public array $bindings = [
        NotificationServiceInterface::class => NotificationService::class,
        NotificationRepositoryInterface::class => EloquentNotificationRepository::class,
    ];

    #[Override]
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::define('has-organization', [OrganizationPolicy::class, 'hasOrganization']);
    }
}
