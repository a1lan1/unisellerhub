<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Override;

class UsersChart extends ChartWidget
{
    protected ?string $heading = 'New User Registrations';

    protected ?string $pollingInterval = '15s';

    #[Override]
    protected function getData(): array
    {
        $data = Trend::model(User::class)
            ->between(
                start: now()->subMonth(),
                end: now(),
            )
            ->perDay()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Users Registered',
                    'data' => $data->map(fn (TrendValue $value): mixed => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value): string => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
