<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                FileUpload::make('avatar_url')
                                    ->label(__('user.avatar'))
                                    ->helperText(__('user.avatar_helper'))
                                    ->image()
                                    ->imageEditor()
                                    ->imageEditorAspectRatios(['1:1'])
                                    ->imageCropAspectRatio('1:1')
                                    ->disk('public')
                                    ->directory('avatar_upload')
                                    ->visibility('public')
                                    ->columnSpanFull(),
                            ]),
                        Section::make()
                            ->schema([
                                Select::make('roles')
                                    ->label(__('user.role'))
                                    ->placeholder(__('user.select_role'))
                                    ->relationship('roles', 'name')
                                    ->native(false)
                                    ->preload()
                                    ->multiple()
                                    ->columnSpanFull()
                                    ->searchable()
                                    ->required(),
                            ]),
                    ])
                    ->columnSpan([
                        'default' => 3,
                        'sm' => 3,
                        'md' => 3,
                        'lg' => 4,
                        'xl' => 1,
                        '2xl' => 1,
                    ])
                    ->columns(1),

                Section::make(__('user.personal_info'))
                    ->description(__('user.user_information_desc'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('user.name'))
                            ->placeholder(__('user.name_placeholder'))
                            ->inlineLabel()
                            ->columnSpanFull()
                            ->required()
                            ->minLength(3)
                            ->maxLength(45)
                            ->autofocus(),

                        TextInput::make('email')
                            ->label(__('user.email'))
                            ->placeholder(__('user.email_placeholder'))
                            ->inlineLabel()
                            ->columnSpanFull()
                            ->email()
                            ->required()
                            ->minLength(3)
                            ->maxLength(45)
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->label(
                                fn($record) =>
                                $record ? __('user.password_edit') : __('user.password')
                            )
                            ->placeholder(
                                fn($record) =>
                                $record ? __('user.password_optional') : __('user.password_placeholder')
                            )
                            ->inlineLabel()
                            ->columnSpanFull()
                            ->password()
                            ->revealable(filament()->arePasswordsRevealable())
                            ->rule(Password::default())
                            ->autocomplete('new-password')
                            ->dehydrated(fn($state): bool => filled($state))
                            ->dehydrateStateUsing(fn($state): string => Hash::make($state))
                            ->live(debounce: 500)
                            ->same('passwordConfirmation')
                            ->required(fn($record) => is_null($record)),

                        TextInput::make('passwordConfirmation')
                            ->label(__('user.password_confirm'))
                            ->placeholder(__('user.password_confirm_placeholder'))
                            ->inlineLabel()
                            ->columnSpanFull()
                            ->password()
                            ->revealable(filament()->arePasswordsRevealable())
                            ->required()
                            ->visible(fn(Get $get): bool => filled($get('password')))
                            ->dehydrated(false),
                    ])->columnSpan([
                            'default' => fn(?User $record) => $record === null ? 3 : 3,
                            'sm' => fn(?User $record) => $record === null ? 2 : 3,
                            'md' => fn(?User $record) => $record === null ? 3 : 3,
                            'lg' => fn(?User $record) => $record === null ? 4 : 4,
                            'xl' => fn(?User $record) => $record === null ? 3 : 2,
                            '2xl' => fn(?User $record) => $record === null ? 3 : 2,
                        ])
                    ->columns(2),

                Section::make()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label(__('user.created_at'))
                            ->formatStateUsing(fn(User $record): ?string => $record->created_at?->diffForHumans()),

                        TextEntry::make('updated_at')
                            ->label(__('user.updated_at'))
                            ->formatStateUsing(fn(User $record): ?string => $record->updated_at?->diffForHumans()),
                    ])
                    ->columnSpan([
                        'default' => 3,
                        'sm' => 3,
                        'md' => 3,
                        'lg' => 4,
                        'xl' => 1,
                        '2xl' => 1,
                    ])
                    ->hidden(fn(?User $record) => $record === null)
            ])->columns(4);
    }
}
