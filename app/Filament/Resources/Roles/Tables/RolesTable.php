<?php

namespace App\Filament\Resources\Roles\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Size;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('role.name'))
                    ->searchable(),

                TextColumn::make('permissions.name')
                    ->label(__('role.permissions'))
                    ->placeholder(__('role.no_permissions'))
                    ->colors([
                        'info',
                    ])
                    ->badge()
                    ->separator(', ')
                    ->limitList(4)
                    ->wrap(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label(__('role.edit_role'))
                        ->icon(Heroicon::PencilSquare),
                    DeleteAction::make()
                        ->label(__('role.delete_role'))
                        ->icon(Heroicon::Trash)
                        ->hidden(function ($record) {
                            // Ambil semua role ids yang dimiliki user saat ini
                            $userRoleIds = Auth::user()->roles->pluck('id')->toArray();
                            // Sembunyikan tombol jika role ini termasuk milik user
                            return in_array($record->id, $userRoleIds);
                        }),
                ])
                    ->label('')
                    ->icon('heroicon-m-ellipsis-horizontal')
                    ->size(Size::Small)
                    ->color('info')
                    ->outlined()
                    ->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
