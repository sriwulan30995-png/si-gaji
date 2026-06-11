<?php

namespace App\Filament\Resources\Permissions\Tables;

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

class PermissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('permission.permission_name'))
                    ->icon(Heroicon::Key)
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('created_at')
                    ->label(__('permission.created_at'))
                    ->icon('heroicon-o-calendar')
                    ->since() // “2 hours ago”
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('permission.updated_at'))
                    ->icon('heroicon-o-clock')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label(__('permission.edit_permission'))
                        ->icon(Heroicon::PencilSquare),
                    DeleteAction::make()
                        ->label(__('permission.delete_permission'))
                        ->icon(Heroicon::Trash),
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
