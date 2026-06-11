<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RoleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label(__('role.name'))
                    ->weight('bold'),

                TextEntry::make('guard_name')
                    ->label(__('role.guard'))
                    ->badge()
                    ->color('gray'),

                TextEntry::make('created_at')
                    ->label(__('role.created_at'))
                    ->dateTime()
                    ->placeholder('-'),

                TextEntry::make('updated_at')
                    ->label(__('role.updated_at'))
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
