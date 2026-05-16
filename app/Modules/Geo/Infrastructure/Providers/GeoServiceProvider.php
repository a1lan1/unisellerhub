<?php

declare(strict_types=1);

namespace App\Modules\Geo\Infrastructure\Providers;

use App\Modules\Geo\Application\Services\GeoCollectorService;
use App\Modules\Geo\Domain\Interfaces\GeoCollectorServiceInterface;
use App\Modules\Geo\Domain\Repositories\LocationRepositoryInterface;
use App\Modules\Geo\Domain\Repositories\ResponseTemplateRepositoryInterface;
use App\Modules\Geo\Domain\Repositories\ReviewRepositoryInterface;
use App\Modules\Geo\Infrastructure\Repositories\LocationRepository;
use App\Modules\Geo\Infrastructure\Repositories\ResponseTemplateRepository;
use App\Modules\Geo\Infrastructure\Repositories\ReviewRepository;
use Illuminate\Support\ServiceProvider;
use Override;

class GeoServiceProvider extends ServiceProvider
{
    /**
     * @var array<string, string>
     */
    public array $bindings = [
        ReviewRepositoryInterface::class => ReviewRepository::class,
        ResponseTemplateRepositoryInterface::class => ResponseTemplateRepository::class,
        LocationRepositoryInterface::class => LocationRepository::class,
    ];

    #[Override]
    public function register(): void
    {
        $this->app->singleton(fn (): GeoCollectorServiceInterface => new GeoCollectorService(
            baseUrl: config('services.geo_collector.url'),
            timeout: config('services.geo_collector.timeout'),
        ));
    }

    public function boot(): void
    {
        //
    }
}
