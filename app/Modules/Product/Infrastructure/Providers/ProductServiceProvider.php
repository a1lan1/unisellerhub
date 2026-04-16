<?php

declare(strict_types=1);

namespace App\Modules\Product\Infrastructure\Providers;

use App\Modules\Product\Domain\Repositories\ProductRepositoryInterface;
use App\Modules\Product\Infrastructure\Repositories\EloquentProductRepository;
use Illuminate\Support\ServiceProvider;
use Override;

class ProductServiceProvider extends ServiceProvider
{
    /**
     * @var array<string, string>
     */
    public array $bindings = [
        ProductRepositoryInterface::class => EloquentProductRepository::class,
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
