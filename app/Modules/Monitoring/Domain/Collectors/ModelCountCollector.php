<?php

declare(strict_types=1);

namespace App\Modules\Monitoring\Domain\Collectors;

use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use App\Modules\User\Domain\Models\Organization;
use App\Modules\User\Domain\Models\User;
use Spatie\Prometheus\Collectors\Collector;
use Spatie\Prometheus\Facades\Prometheus;

class ModelCountCollector implements Collector
{
    public function register(): void
    {
        Prometheus::addGauge('user_count')
            ->helpText('The total number of users.')
            ->value(fn () => User::count());

        Prometheus::addGauge('organization_count')
            ->helpText('The total number of organizations.')
            ->value(fn () => Organization::count());

        Prometheus::addGauge('marketplace_connection_count')
            ->helpText('The total number of marketplace connections.')
            ->value(fn () => MarketplaceConnection::count());
    }
}
