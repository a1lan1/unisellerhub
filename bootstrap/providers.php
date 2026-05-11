<?php

use App\Modules\Activity\Infrastructure\Providers\ActivityServiceProvider;
use App\Modules\Inventory\Infrastructure\Providers\InventoryServiceProvider;
use App\Modules\Marketplace\Infrastructure\Providers\MarketplaceServiceProvider;
use App\Modules\MockMarketplace\Infrastructure\Providers\MockMarketplaceServiceProvider;
use App\Modules\Monitoring\Infrastructure\Providers\PrometheusServiceProvider;
use App\Modules\Order\Infrastructure\Providers\OrderServiceProvider;
use App\Modules\PriceAnalysis\Providers\PriceAnalysisServiceProvider;
use App\Modules\Product\Infrastructure\Providers\ProductServiceProvider;
use App\Modules\Report\Infrastructure\Providers\ReportServiceProvider;
use App\Modules\Shared\Infrastructure\Providers\SharedServiceProvider;
use App\Modules\User\Infrastructure\Providers\FortifyServiceProvider;
use App\Modules\User\Infrastructure\Providers\UserServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\HorizonServiceProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    HorizonServiceProvider::class,
    PrometheusServiceProvider::class,

    // Module Providers
    SharedServiceProvider::class,
    UserServiceProvider::class,
    FortifyServiceProvider::class,
    ActivityServiceProvider::class,
    OrderServiceProvider::class,
    ProductServiceProvider::class,
    InventoryServiceProvider::class,
    MarketplaceServiceProvider::class,
    MockMarketplaceServiceProvider::class,
    ReportServiceProvider::class,
    PriceAnalysisServiceProvider::class,
];
