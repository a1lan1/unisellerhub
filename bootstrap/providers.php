<?php

use App\Modules\MockMarketplace\Infrastructure\Providers\MockMarketplaceServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\HorizonServiceProvider;
use App\Providers\PrometheusServiceProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    FortifyServiceProvider::class,
    HorizonServiceProvider::class,
    PrometheusServiceProvider::class,

    // Module Providers
    MockMarketplaceServiceProvider::class,
];
