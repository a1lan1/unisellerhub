<?php

declare(strict_types=1);

namespace App\Modules\Order\Infrastructure\Providers;

use App\Modules\Order\Application\Services\OrderService;
use App\Modules\Order\Domain\Interfaces\OrderServiceInterface;
use App\Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use App\Modules\Order\Infrastructure\Repositories\EloquentOrderRepository;
use Illuminate\Support\ServiceProvider;
use Override;

class OrderServiceProvider extends ServiceProvider
{
    /**
     * @var array<string, string>
     */
    public array $bindings = [
        OrderServiceInterface::class => OrderService::class,
        OrderRepositoryInterface::class => EloquentOrderRepository::class,
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
