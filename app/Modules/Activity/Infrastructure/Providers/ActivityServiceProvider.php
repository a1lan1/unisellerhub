<?php

declare(strict_types=1);

namespace App\Modules\Activity\Infrastructure\Providers;

use App\Modules\Activity\Application\Services\ActivityLoggerService;
use App\Modules\Activity\Application\Services\ActivityService;
use App\Modules\Activity\Domain\Interfaces\ActivityLoggerInterface;
use App\Modules\Activity\Domain\Interfaces\ActivityServiceInterface;
use App\Modules\Activity\Domain\Repositories\ActivityRepositoryInterface;
use App\Modules\Activity\Infrastructure\Repositories\ActivityRepository;
use Illuminate\Support\ServiceProvider;
use Override;

class ActivityServiceProvider extends ServiceProvider
{
    /**
     * @var array<string, string>
     */
    public array $bindings = [
        ActivityServiceInterface::class => ActivityService::class,
        ActivityLoggerInterface::class => ActivityLoggerService::class,
        ActivityRepositoryInterface::class => ActivityRepository::class,
    ];

    #[Override]
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }
}
