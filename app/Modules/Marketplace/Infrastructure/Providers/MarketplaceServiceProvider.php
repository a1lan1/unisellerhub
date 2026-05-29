<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Infrastructure\Providers;

use App\Modules\Marketplace\Application\Services\MarketplaceConnectionService;
use App\Modules\Marketplace\Domain\Interfaces\MarketplaceConnectionServiceInterface;
use App\Modules\Marketplace\Domain\Repositories\MarketplaceConnectionRepositoryInterface;
use App\Modules\Marketplace\Infrastructure\Repositories\MarketplaceConnectionRepository;
use Illuminate\Support\ServiceProvider;
use Override;

class MarketplaceServiceProvider extends ServiceProvider
{
    /**
     * @var array<string, string>
     */
    public array $bindings = [
        MarketplaceConnectionServiceInterface::class => MarketplaceConnectionService::class,
        MarketplaceConnectionRepositoryInterface::class => MarketplaceConnectionRepository::class,
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
