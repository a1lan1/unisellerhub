<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Infrastructure\Providers;

use App\Modules\MockMarketplace\Domain\Repositories\MockOrderRepositoryInterface;
use App\Modules\MockMarketplace\Domain\Repositories\MockProductRepositoryInterface;
use App\Modules\MockMarketplace\Domain\Repositories\MockStockRepositoryInterface;
use App\Modules\MockMarketplace\Infrastructure\Repositories\EloquentMockOrderRepository;
use App\Modules\MockMarketplace\Infrastructure\Repositories\EloquentMockProductRepository;
use App\Modules\MockMarketplace\Infrastructure\Repositories\EloquentMockStockRepository;
use Illuminate\Support\ServiceProvider;
use Override;

class MockMarketplaceServiceProvider extends ServiceProvider
{
    /**
     * @var array<string, string>
     */
    public array $bindings = [
        MockStockRepositoryInterface::class => EloquentMockStockRepository::class,
        MockOrderRepositoryInterface::class => EloquentMockOrderRepository::class,
        MockProductRepositoryInterface::class => EloquentMockProductRepository::class,
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
