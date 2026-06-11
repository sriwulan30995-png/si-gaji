<?php

namespace App\Filament\Resources\Permissions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PermissionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label(__('permission.permission_name')),
                TextEntry::make('guard_name')
                    ->label(__('permission.guard_name')),
                TextEntry::make('created_at')
                    ->label(__('permission.created_at'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label(__('permission.updated_at'))
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
