<?php

namespace App\Filament\Pages;

use Caresome\FilamentAuthDesigner\Pages\Auth\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;

class Login extends BaseLogin
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('email')
                    ->label('Email')
                    ->placeholder('Masukkan Email')
                    ->email()
                    ->required()
                    ->autocomplete()
                    ->autofocus()
                    ->extraInputAttributes(['tabindex' => 1]),

                TextInput::make('password')
                    ->label('Kata Sandi')
                    ->placeholder('Masukkan Kata Sandi')
                    ->hint(filament()->hasPasswordReset() ? new HtmlString(Blade::render('<x-filament::link :href="filament()->getRequestPasswordResetUrl()" tabindex="3"> {{ __(\'filament-panels::pages/auth/login.actions.request_password_reset.label\') }}</x-filament::link>')) : null)
                    ->password()
                    ->revealable(filament()->arePasswordsRevealable())
                    ->autocomplete('current-password')
                    ->required()
                    ->extraInputAttributes(['tabindex' => 2]),
            ]);
    }
}
