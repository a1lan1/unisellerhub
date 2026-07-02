<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Modules\Inventory\Domain\Models\Inventory;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Product\Domain\Models\Product;
use App\Modules\Product\Domain\Models\ProductListing;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Override;

class InventoryRadarSummary extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected ?string $pollingInterval = '10s';

    #[Override]
    protected function getStats(): array
    {
        $stats = Cache::remember('inventory_radar_summary', 120, fn (): array => [
            'products' => Product::count(),
            'listings' => ProductListing::count(),
            'warehouses' => Warehouse::count(),
            'inventory' => Inventory::count(),
        ]);

        return [
            Stat::make('Products', $stats['products'])
                ->icon('heroicon-o-cube'),

            Stat::make('Listings', $stats['listings'])
                ->icon('heroicon-o-tag'),

            Stat::make('Warehouses', $stats['warehouses'])
                ->icon('heroicon-o-building-office'),

            Stat::make('Inventory', $stats['inventory'])
                ->icon('heroicon-o-archive-box'),
        ];
    }

    public static function clearCache(): void
    {
        Cache::forget('inventory_radar_summary');
    }
}
