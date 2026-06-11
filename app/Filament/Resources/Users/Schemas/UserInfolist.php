<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;


class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                /*
                |--------------------------------------------------------------------------
                | Section: Profil Pengguna
                |--------------------------------------------------------------------------
                */
                Section::make(__('user.profile_section'))
                    ->description(__('user.profile_section_desc'))
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                ImageEntry::make('avatar_url')
                                    ->label(__('user.avatar'))
                                    ->disk('public')
                                    ->visibility('public')
                                    ->circular()
                                    ->defaultImageUrl(
                                        fn($record) =>
                                        'https://ui-avatars.com/api/?name=' .
                                        urlencode($record->name) .
                                        '&background=030712&color=FFFFFF'
                                    )
                                    ->columnSpan(1),

                                Grid::make(1)
                                    ->schema([
                                        TextEntry::make('name')
                                            ->label(__('user.name'))
                                            ->inlineLabel()
                                            ->weight('bold'),

                                        TextEntry::make('email')
                                            ->label(__('user.email'))
                                            ->inlineLabel()
                                            ->icon('heroicon-o-envelope')
                                            ->copyable()
                                            ->copyMessage(__('user.email_copied'))
                                            ->color('primary'),
                                    ])
                                    ->columnSpan(2),
                            ]),
                    ])
                    ->columnSpan(1),

                Group::make([
                    /*
                    |--------------------------------------------------------------------------
                    | Section: Informasi Akun
                    |--------------------------------------------------------------------------
                    */
                    Section::make(__('user.account_section'))
                        ->description(__('user.account_section_desc'))
                        ->icon('heroicon-o-shield-check')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TextEntry::make('roles.name')
                                        ->label(__('user.role'))
                                        ->badge()
                                        ->separator(', ')
                                        ->color('info'),

                                    TextEntry::make('email_verified_at')
                                        ->label(__('user.email_verification_status'))
                                        ->placeholder(__('user.email_verification_status_placeholder'))
                                        ->badge()
                                        ->formatStateUsing(
                                            fn($state) => $state
                                            ? __('user.email_verified')
                                            : __('user.email_not_verified')
                                        )
                                        ->color(fn($state) => $state ? 'success' : 'danger'),
                                ]),
                        ]),

                    /*
                    |--------------------------------------------------------------------------
                    | Section: Metadata Sistem
                    |--------------------------------------------------------------------------
                    */
                    Section::make(__('user.meta_section'))
                        ->description(__('user.meta_section_desc'))
                        ->icon('heroicon-o-clock')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TextEntry::make('created_at')
                                        ->label(__('user.created_at'))
                                        ->icon('heroicon-o-calendar')
                                        ->formatStateUsing(
                                            fn($state) => $state?->diffForHumans() ?? '-'
                                        ),

                                    TextEntry::make('updated_at')
                                        ->label(__('user.updated_at'))
                                        ->icon('heroicon-o-arrow-path')
                                        ->formatStateUsing(
                                            fn($state) => $state?->diffForHumans() ?? '-'
                                        ),
                                ]),
                        ]),
                ])

            ]);
    }
}
