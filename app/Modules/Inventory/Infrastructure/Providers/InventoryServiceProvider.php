<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Infrastructure\Providers;

use App\Modules\Inventory\Domain\Repositories\InventoryRepositoryInterface;
use App\Modules\Inventory\Domain\Repositories\WarehouseRepositoryInterface;
use App\Modules\Inventory\Infrastructure\Repositories\EloquentInventoryRepository;
use App\Modules\Inventory\Infrastructure\Repositories\EloquentWarehouseRepository;
use Illuminate\Support\ServiceProvider;
use Override;

class InventoryServiceProvider extends ServiceProvider
{
    /**
     * @var array<string, string>
     */
    public array $bindings = [
        InventoryRepositoryInterface::class => EloquentInventoryRepository::class,
        WarehouseRepositoryInterface::class => EloquentWarehouseRepository::class,
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
