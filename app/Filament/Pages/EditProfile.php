<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('EditProfile')
                    ->tabs([
                        Tab::make('Informasi Pribadi')
                            ->schema([
                                FileUpload::make('avatar_url')
                                    ->label(__('filament-panels::pages/auth/edit-profile.form.avatar.label'))
                                    ->image()
                                    ->imageEditor()
                                    ->imageEditorAspectRatios([
                                        '1:1',
                                    ])
                                    ->imageCropAspectRatio('1:1')
                                    ->directory('avatar_upload')
                                    ->visibility('public')
                                    ->helperText(__('filament-panels::pages/auth/edit-profile.form.avatar.helper'))
                                    ->columnSpanFull(),

                                TextInput::make('name')
                                    ->label(__('filament-panels::pages/auth/edit-profile.form.name.label'))
                                    ->placeholder(__('filament-panels::pages/auth/edit-profile.form.name.placeholder'))
                                    ->inlineLabel()
                                    ->required()
                                    ->maxLength(255)
                                    ->autofocus(),

                                TextInput::make('email')
                                    ->label(__('filament-panels::pages/auth/edit-profile.form.email.label'))
                                    ->placeholder(__('filament-panels::pages/auth/edit-profile.form.email.placeholder'))
                                    ->inlineLabel()
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                            ]),

                        Tab::make('Kata Sandi')
                            ->schema([
                                TextInput::make('password')
                                    ->label(__('filament-panels::pages/auth/edit-profile.form.password.label'))
                                    ->placeholder(__('filament-panels::pages/auth/edit-profile.form.password.placeholder'))
                                    ->password()
                                    ->revealable(filament()->arePasswordsRevealable())
                                    ->rule(Password::default())
                                    ->autocomplete('new-password')
                                    ->dehydrated(fn($state): bool => filled($state))
                                    ->dehydrateStateUsing(fn($state): string => Hash::make($state))
                                    ->live(debounce: 500)
                                    ->same('passwordConfirmation'),

                                TextInput::make('passwordConfirmation')
                                    ->label(__('filament-panels::pages/auth/edit-profile.form.password_confirmation.label'))
                                    ->placeholder(__('filament-panels::pages/auth/edit-profile.form.password_confirmation.placeholder'))
                                    ->password()
                                    ->revealable(filament()->arePasswordsRevealable())
                                    ->required()
                                    ->visible(fn(Get $get): bool => filled($get('password')))
                                    ->dehydrated(false),
                            ])
                    ]),
            ]);
    }
}
