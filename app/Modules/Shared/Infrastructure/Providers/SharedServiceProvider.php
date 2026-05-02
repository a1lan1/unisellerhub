<?php

declare(strict_types=1);

namespace App\Modules\Shared\Infrastructure\Providers;

use App\Modules\Shared\Application\Services\SyncResultProcessorService;
use App\Modules\Shared\Application\Services\TenantManager;
use App\Modules\Shared\Domain\Enums\QueueNameEnum;
use App\Modules\Shared\Domain\Interfaces\RabbitMQConnectionInterface;
use App\Modules\Shared\Domain\Interfaces\SyncResultProcessorInterface;
use App\Modules\Shared\Infrastructure\Services\RabbitMQConnectionService;
use Illuminate\Support\ServiceProvider;
use Override;

class SharedServiceProvider extends ServiceProvider
{
    /**
     * @var array<string, string>
     */
    public array $bindings = [
        RabbitMQConnectionInterface::class => RabbitMQConnectionService::class,
        SyncResultProcessorInterface::class => SyncResultProcessorService::class,
    ];

    #[Override]
    public function register(): void
    {
        $this->app->singleton(TenantManager::class);
    }

    public function boot(RabbitMQConnectionInterface $rabbitMQConnectionService): void
    {
        // Declare queues that Horizon will listen to, to prevent "not_found" errors
        $rabbitMQConnectionService->declareQueues([
            QueueNameEnum::Default->value,
            QueueNameEnum::HighPriority->value,
            QueueNameEnum::LowPriority->value,
            QueueNameEnum::MeilisearchTasks->value,
        ]);
    }
}
