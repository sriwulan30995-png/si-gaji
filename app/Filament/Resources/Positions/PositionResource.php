<?php

namespace App\Filament\Resources\Positions;

use App\Filament\Resources\Positions\Pages\ManagePositions;
use App\Models\Position;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\Size;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class PositionResource extends Resource
{
    protected static ?string $model = Position::class;

    // --- Konfigurasi Navigasi (UI/UX) ---
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;
    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Briefcase;

    protected static ?string $navigationLabel = 'Jabatan';
    protected static ?string $modelLabel = 'Jabatan';
    protected static ?string $pluralModelLabel = 'Daftar Jabatan';

    // Langsung tembak pakai string, lebih simpel!
    protected static string|UnitEnum|null $navigationGroup = 'Data Master';

    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'position_name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Jabatan')
                    ->description('Tentukan nama jabatan beserta standar kompensasi dasarnya.')
                    ->icon(Heroicon::OutlinedIdentification)
                    ->schema([
                        TextInput::make('position_name')
                            ->label('Nama Jabatan')
                            ->placeholder('Contoh: Senior Web Developer')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(), // Membuat input ini penuh 1 baris

                        Grid::make(2) // Membagi baris menjadi 2 kolom
                            ->schema([
                                TextInput::make('base_salary')
                                    ->label('Gaji Pokok')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->step(1000), // Memudahkan input dengan panah atas/bawah

                                TextInput::make('hourly_overtime_rate')
                                    ->label('Tarif Lembur per Jam')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rp'),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Jabatan')
                    ->icon(Heroicon::OutlinedInformationCircle)
                    ->schema([
                        TextEntry::make('position_name')
                            ->label('Nama Jabatan')
                            ->weight(FontWeight::Bold),

                        TextEntry::make('base_salary')
                            ->label('Gaji Pokok')
                            ->money('IDR', locale: 'id'), // Otomatis format Rp xxx.xxx

                        TextEntry::make('hourly_overtime_rate')
                            ->label('Tarif Lembur/Jam')
                            ->money('IDR', locale: 'id'),

                        TextEntry::make('created_at')
                            ->label('Didaftarkan Pada')
                            ->dateTime('d M Y, H:i')
                            ->color('gray'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('position_name')
                    ->label('Jabatan')
                    ->searchable()
                    ->weight(FontWeight::SemiBold) // Teks sedikit ditebalkan agar clean
                    ->icon(Heroicon::OutlinedBriefcase),

                TextColumn::make('base_salary')
                    ->label('Gaji Pokok')
                    ->money('IDR', locale: 'id') // Format mata uang Rupiah
                    ->sortable(),

                TextColumn::make('hourly_overtime_rate')
                    ->label('Tarif Lembur')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->color('success'), // Memberikan warna hijau agar pembeda visual lebih baik

                TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePositions::route('/'),
        ];
    }
}