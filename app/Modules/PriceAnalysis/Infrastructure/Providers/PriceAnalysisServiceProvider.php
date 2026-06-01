<?php

declare(strict_types=1);

namespace App\Modules\PriceAnalysis\Infrastructure\Providers;

use App\Modules\PriceAnalysis\Application\Services\PriceAnalysisSyncResultProcessor;
use App\Modules\PriceAnalysis\Domain\Interfaces\PriceAnalysisSyncResultProcessorInterface;
use App\Modules\PriceAnalysis\Domain\Repositories\PriceAnalysisRepositoryInterface;
use App\Modules\PriceAnalysis\Infrastructure\Repositories\EloquentPriceAnalysisRepository;
use Illuminate\Support\ServiceProvider;
use Override;

final class PriceAnalysisServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array<string, string>
     */
    public array $bindings = [
        PriceAnalysisRepositoryInterface::class => EloquentPriceAnalysisRepository::class,
    ];

    /**
     * Register any application services.
     */
    #[Override]
    public function register(): void
    {
        $this->app->singleton(fn (): PriceAnalysisSyncResultProcessorInterface => new PriceAnalysisSyncResultProcessor);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
