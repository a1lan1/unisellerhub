<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Enums\RoleEnum;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ViewRecord;
use Override;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
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
        ];
    }
}
