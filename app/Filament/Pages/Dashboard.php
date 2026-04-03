<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\UsersChart;
use Filament\Pages\Dashboard as BaseDashboard;
use Override;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';

    #[Override]
    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverview::class,
        ];
    }

    #[Override]
    public function getWidgets(): array
    {
        return [
            UsersChart::class,
        ];
    }
}
