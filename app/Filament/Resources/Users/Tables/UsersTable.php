<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use App\Enums\MediaCollection;
use App\Enums\RoleEnum;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('avatar')
                    ->collection(MediaCollection::UserAvatar->value)
                    ->circular(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->formatStateUsing(fn ($state): string => Str::headline($state))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        RoleEnum::ADMIN->value => 'danger',
                        RoleEnum::USER->value => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->options(Role::pluck('name', 'id'))
                    ->multiple()
                    ->label('Role'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('changeRole')
                    ->label('Change Role')
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        Select::make('roles')
                            ->label('Roles')
                            ->options(RoleEnum::class)
                            ->multiple()
                            ->default(fn (User $record): array => $record->roles->pluck('name')->toArray())
                            ->required(),
                    ])
                    ->action(function (User $record, array $data): void {
                        $record->syncRoles($data['roles']);
                    })
                    ->visible(fn (): bool => auth()->user()?->isAdmin()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
