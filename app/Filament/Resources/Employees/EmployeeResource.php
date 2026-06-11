<?php

namespace App\Filament\Resources\Employees;

use App\Filament\Resources\Employees\Pages\ManageEmployees;
use App\Models\Employee;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    // --- Konfigurasi Navigasi (UI/UX) ---
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;
    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::UserGroup;

    protected static ?string $navigationLabel = 'Karyawan';
    protected static ?string $modelLabel = 'Karyawan';
    protected static ?string $pluralModelLabel = 'Daftar Karyawan';

    // Langsung menggunakan string sesuai pola sebelumnya
    protected static string|UnitEnum|null $navigationGroup = 'Data Master';

    protected static ?int $navigationSort = 2; // Di bawah Jabatan (1)
    protected static ?string $recordTitleAttribute = 'full_name'; // Ganti dari 'name' ke 'full_name' sesuai model Anda
    protected static ?string $slug = 'karyawan';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Personal')
                    ->description('Data diri karyawan dan profil pajak.')
                    ->icon(Heroicon::OutlinedUserCircle)
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('nik')
                                ->label('Nomor Induk Karyawan (NIK)')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(20)
                                ->placeholder('Contoh: 2026001'),

                            TextInput::make('full_name')
                                ->label('Nama Lengkap')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('Nama sesuai KTP'),
                        ]),

                        Select::make('status_ptkp')
                            ->label('Status PTKP (Pajak)')
                            ->options([
                                'TK/0' => 'Tidak Kawin / 0 Tanggungan (TK/0)',
                                'K/0' => 'Kawin / 0 Tanggungan (K/0)',
                                'K/1' => 'Kawin / 1 Tanggungan (K/1)',
                                'K/2' => 'Kawin / 2 Tanggungan (K/2)',
                                'K/3' => 'Kawin / 3 Tanggungan (K/3)',
                            ])
                            ->required()
                            ->searchable(),
                    ])->columnSpanFull(),

                Section::make('Penempatan & Akun')
                    ->description('Tentukan jabatan dan tautkan dengan akun login sistem.')
                    ->icon(Heroicon::OutlinedBriefcase)
                    ->schema([
                        Grid::make(2)->schema([
                            // UX HACK: Mengubah numeric ID menjadi Dropdown yang mengambil nama jabatan
                            Select::make('position_id')
                                ->label('Jabatan')
                                ->relationship('position', 'position_name')
                                ->required()
                                ->searchable()
                                ->preload(),

                            // UX HACK: Mengubah numeric ID menjadi Dropdown akun user
                            Select::make('user_id')
                                ->label('Akun Login Terkait')
                                ->relationship('user', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->helperText('Pilih akun agar karyawan bisa login melihat slip gaji.'),
                        ]),

                        Grid::make(2)->schema([
                            DatePicker::make('joining_date')
                                ->label('Tanggal Bergabung')
                                ->required()
                                ->native(false) // UX: Menggunakan pop-up kalender bawaan Filament yang lebih cantik
                                ->displayFormat('d F Y'),

                            Toggle::make('is_active')
                                ->label('Status Karyawan Aktif')
                                ->default(true)
                                ->helperText('Matikan jika karyawan sudah resign.'),
                        ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Karyawan')
                    ->schema([
                        TextEntry::make('nik')
                            ->label('NIK')
                            ->weight(FontWeight::Bold),
                        TextEntry::make('full_name')
                            ->label('Nama Lengkap'),
                        TextEntry::make('position.position_name') // Ambil relasi
                            ->label('Jabatan')
                            ->badge()
                            ->color('info'),
                        TextEntry::make('status_ptkp')
                            ->label('Status PTKP'),
                        TextEntry::make('joining_date')
                            ->label('Tanggal Bergabung')
                            ->date('d F Y'),
                        IconEntry::make('is_active')
                            ->label('Status Aktif')
                            ->boolean(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('nik')
                    ->label('NIK')
                    ->searchable()
                    ->sortable()
                    ->color('gray'),

                TextColumn::make('full_name')
                    ->label('Nama Karyawan')
                    ->searchable()
                    ->weight(FontWeight::SemiBold),

                // UX HACK: Tampilkan Nama Jabatan, BUKAN ID-nya
                TextColumn::make('position.position_name')
                    ->label('Jabatan')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('status_ptkp')
                    ->label('PTKP')
                    ->toggleable(isToggledHiddenByDefault: true), // Sembunyikan jika layar sempit

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon(Heroicon::CheckCircle)
                    ->falseIcon(Heroicon::XCircle),

                TextColumn::make('joining_date')
                    ->label('Mulai Kerja')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('position_id')
                    ->label('Filter Jabatan')
                    ->relationship('position', 'position_name'),

                TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->trueLabel('Karyawan Aktif')
                    ->falseLabel('Non-Aktif / Resign'),
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
            ->emptyStateHeading('Belum ada data Karyawan')
            ->emptyStateDescription('Daftarkan karyawan Anda untuk mulai menghitung kehadiran dan gaji.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageEmployees::route('/'),
        ];
    }
}
