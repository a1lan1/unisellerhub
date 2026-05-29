<?php

declare(strict_types=1);

namespace App\Modules\Product\Infrastructure\Providers;

use App\Modules\Product\Application\Services\ProductService;
use App\Modules\Product\Domain\Interfaces\ProductServiceInterface;
use App\Modules\Product\Domain\Repositories\ProductListingRepositoryInterface;
use App\Modules\Product\Domain\Repositories\ProductRepositoryInterface;
use App\Modules\Product\Infrastructure\Repositories\EloquentProductListingRepository;
use App\Modules\Product\Infrastructure\Repositories\EloquentProductRepository;
use Illuminate\Support\ServiceProvider;
use Override;

class ProductServiceProvider extends ServiceProvider
{
    /**
     * @var array<string, string>
     */
    public array $bindings = [
        ProductServiceInterface::class => ProductService::class,
        ProductRepositoryInterface::class => EloquentProductRepository::class,
        ProductListingRepositoryInterface::class => EloquentProductListingRepository::class,
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
