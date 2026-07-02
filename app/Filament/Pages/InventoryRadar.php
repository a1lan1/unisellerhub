<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class InventoryRadar extends Page
{
    protected static string|null|BackedEnum $navigationIcon = Heroicon::OutlinedCube;

    protected string $view = 'filament.pages.inventory-radar';
}
