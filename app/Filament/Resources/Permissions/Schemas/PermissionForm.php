<?php

namespace App\Filament\Resources\Permissions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class PermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('permission.permission_information'))
                    ->description(__('permission.permission_information_desc'))
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        TextInput::make('name')
                            ->label(__('permission.permission_name'))
                            ->placeholder(__('permission.permission_placeholder'))
                            ->helperText(
                                new HtmlString(__('permission.helper_text_permission'))
                            )
                            ->prefixIcon('heroicon-o-lock-closed')
                            ->required()
                            ->minLength(3)
                            ->maxLength(45)
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->columnSpanFull(),
            ]);
    }
}
