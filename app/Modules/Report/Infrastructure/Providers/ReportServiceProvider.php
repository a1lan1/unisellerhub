<?php

declare(strict_types=1);

namespace App\Modules\Report\Infrastructure\Providers;

use App\Modules\Report\Application\Services\AnalyticsService;
use App\Modules\Report\Domain\Interfaces\AnalyticsServiceInterface;
use App\Modules\Report\Domain\Repositories\AnalyticsRepositoryInterface;
use App\Modules\Report\Infrastructure\Repositories\DatabaseAnalyticsRepository;
use Illuminate\Support\ServiceProvider;
use Override;

class ReportServiceProvider extends ServiceProvider
{
    /**
     * @var array<string, string>
     */
    public array $bindings = [
        AnalyticsServiceInterface::class => AnalyticsService::class,
        AnalyticsRepositoryInterface::class => DatabaseAnalyticsRepository::class,
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
