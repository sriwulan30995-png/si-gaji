<?php

namespace App\Filament\Resources\AllowanceTypes;

use App\Filament\Resources\AllowanceTypes\Pages\ManageAllowanceTypes;
use App\Models\AllowanceType;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class AllowanceTypeResource extends Resource
{
    protected static ?string $model = AllowanceType::class;
    // --- Konfigurasi Navigasi ---
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;
    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Banknotes;

    protected static ?string $navigationLabel = 'Jenis Tunjangan';
    protected static ?string $modelLabel = 'Jenis Tunjangan';
    protected static ?string $pluralModelLabel = 'Kategori Tunjangan';

    // Menggunakan string langsung sesuai permintaan Anda
    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan Gaji';

    protected static ?int $navigationSort = 3; // Di bawah Karyawan
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $slug = 'jenis-tunjangan';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Definisi Tunjangan')
                    ->description('Buat kategori tunjangan baru untuk nantinya ditambahkan ke jabatan tertentu.')
                    ->icon(Heroicon::OutlinedTag)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Tunjangan')
                            ->placeholder('Contoh: Tunjangan Makan, Tunjangan Transport, dll.')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Toggle::make('is_daily')
                            ->label('Dihitung Berdasarkan Hari Hadir (Harian)?')
                            ->helperText('Aktifkan jika nominal tunjangan ini dikalikan dengan jumlah kehadiran karyawan (contoh: Uang Makan). Biarkan mati jika dibayar penuh sebulan.')
                            ->default(false),
                    ])->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Jenis Tunjangan')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nama Tunjangan')
                            ->weight(FontWeight::Bold),

                        IconEntry::make('is_daily')
                            ->label('Sifat Perhitungan')
                            ->boolean()
                            ->trueIcon(Heroicon::CalendarDays) // UX: Ikon kalender untuk "Harian"
                            ->falseIcon(Heroicon::Banknotes),   // UX: Ikon uang untuk "Tetap"

                        TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime('d M Y')
                            ->color('gray'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Tunjangan')
                    ->searchable()
                    ->weight(FontWeight::SemiBold)
                    ->icon(Heroicon::OutlinedBanknotes),

                // UX HACK: Mengubah tampilan Boolean biasa menjadi Badge Teks yang jelas
                TextColumn::make('is_daily')
                    ->label('Sifat Pencairan')
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Harian (x Kehadiran)' : 'Tetap (Bulanan)')
                    ->badge()
                    ->color(fn(bool $state): string => $state ? 'info' : 'success'),

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
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
                    ->icon(Heroicon::OutlinedEllipsisVertical)
                    ->tooltip('Opsi'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada jenis tunjangan')
            ->emptyStateDescription('Tambahkan kategori tunjangan baru seperti Uang Makan atau Transport.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAllowanceTypes::route('/'),
        ];
    }
}
