@php
 use App\Filament\Widgets\CriticalInventory;
 use App\Filament\Widgets\InventoryRadarSummary;
 use App\Filament\Widgets\OutOfStockInventory;
@endphp

<x-filament-panels::page>
    <x-filament-widgets::widgets
        :widgets="[
        InventoryRadarSummary::class,
        CriticalInventory::class,
        OutOfStockInventory::class,
    ]"
    />
</x-filament-panels::page>
