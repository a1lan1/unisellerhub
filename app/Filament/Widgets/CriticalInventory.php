<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Modules\Inventory\Domain\Models\Inventory;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Override;

class CriticalInventory extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = '5s';

    #[Override]
    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Inventory::query()
                ->whereRaw('(quantity - reserved) > 0')
                ->whereRaw('(quantity - reserved) <= 5')
                ->orderByRaw('(quantity - reserved) ASC')
            )
            ->columns([
                TextColumn::make('listing.product.name')
                    ->label('Product')
                    ->searchable(),

                TextColumn::make('warehouse.name')
                    ->label('Warehouse'),

                TextColumn::make('available')
                    ->label('Available')
                    ->state(fn (Inventory $record): int => $record->available)
                    ->color(fn ($state): string => $state <= 1 ? 'danger' : 'warning')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
