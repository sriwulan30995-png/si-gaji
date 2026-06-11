<?php

namespace App\Filament\Resources\Attendances;

use App\Filament\Resources\Attendances\Pages\ManageAttendances;
use App\Models\Attendance;
use App\Models\Employee;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    // --- Konfigurasi Navigasi (UI/UX) ---
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;
    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::ClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'Kehadiran';
    protected static ?string $modelLabel = 'Data Kehadiran';
    protected static ?string $pluralModelLabel = 'Riwayat Absensi';

    // Masuk ke dalam grup Transaksi bersama dengan Lembur
    protected static string|UnitEnum|null $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 5;
    protected static ?string $slug = 'kehadiran';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kehadiran')
                    ->description('Catat atau ubah data absensi harian karyawan.')
                    ->icon(Heroicon::OutlinedCalendarDays)
                    ->schema([
                        Grid::make(2)->schema([
                            // UX HACK: Menggunakan Select Dropdown untuk nama karyawan
                            Select::make('employee_id')
                                ->label('Nama Karyawan')
                                ->relationship('employee', 'full_name')
                                ->required()
                                ->searchable()
                                ->preload()
                                // 1. Isi otomatis dengan ID Karyawan yang sedang login
                                ->default(function () {
                                    if (Auth::user()->hasRole('Karyawan')) {
                                        // Cari data Employee yang user_id-nya sama dengan user login
                                        return Employee::where('user_id', Auth::id())->value('id');
                                    }
                                    return null;
                                })
                                // 2. Kunci (Disable) dropdown ini jika yang login adalah Karyawan
                                ->disabled(fn() => Auth::user()->hasRole('Karyawan'))
                                // 3. Wajib ditambahkan agar nilai yang di-disable tetap disimpan ke Database
                                ->dehydrated(),

                            DatePicker::make('date')
                                ->label('Tanggal Kehadiran')
                                ->required()
                                ->native(false)
                                ->displayFormat('d F Y')
                                ->default(now())
                                ->closeOnDateSelection(),

                            Select::make('status')
                                ->label('Status Absensi')
                                ->options([
                                    'present' => 'Hadir',
                                    'sick' => 'Sakit',
                                    'leave' => 'Izin',
                                    'absent' => 'Alpa / Tanpa Keterangan',
                                ])
                                ->required()
                                ->hidden()
                                ->dehydratedWhenHidden()
                                ->default('present')
                                ->native(false),
                        ]),

                        Grid::make(2)->schema([
                            DateTimePicker::make('clock_in')
                                ->label('Waktu Masuk (Clock In)')
                                ->native(false)
                                ->displayFormat('d M Y, H:i')
                                ->default(now())
                                ->helperText('Kosongkan jika karyawan Sakit/Izin/Alpa.'),

                            DateTimePicker::make('clock_out')
                                ->label('Waktu Keluar (Clock Out)')
                                ->native(false)
                                ->displayFormat('d M Y, H:i')
                                ->helperText('Sistem akan mengisi ini saat karyawan pulang.'),
                        ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Absensi')
                    ->schema([
                        TextEntry::make('employee.full_name')
                            ->label('Nama Karyawan')
                            ->weight(FontWeight::Bold),

                        TextEntry::make('date')
                            ->label('Tanggal')
                            ->date('d F Y'),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'present' => 'success',
                                'sick' => 'warning',
                                'leave' => 'info',
                                'absent' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'present' => 'Hadir',
                                'sick' => 'Sakit',
                                'leave' => 'Izin',
                                'absent' => 'Alpa',
                                default => ucfirst($state),
                            }),

                        TextEntry::make('clock_in')
                            ->label('Jam Masuk')
                            ->dateTime('H:i') // Cukup tampilkan jamnya saja karena tanggal sudah ada
                            ->placeholder('Tidak ada catatan'),

                        TextEntry::make('clock_out')
                            ->label('Jam Keluar')
                            ->dateTime('H:i')
                            ->placeholder('Tidak ada catatan'),

                        TextEntry::make('created_at')
                            ->label('Dicatat Pada')
                            ->dateTime('d M Y, H:i')
                            ->color('gray'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc') // Secara otomatis menampilkan absensi terbaru di paling atas
            ->modifyQueryUsing(function (Builder $query) {
                // Cek apakah user yang sedang login memiliki peran 'karyawan'
                if (Auth::user()->hasRole('Karyawan')) {
                    // Filter relasi employee agar user_id cocok dengan ID user yang login
                    $query->whereHas('employee', function (Builder $query) {
                        $query->where('user_id', Auth::id());
                    });
                }
            })
            ->columns([
                TextColumn::make('employee.full_name')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold),

                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                // UX HACK: Badge visual yang sangat krusial untuk HRD melihat siapa yang membolos
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'present' => 'success',  // Hijau
                        'sick' => 'warning',  // Kuning
                        'leave' => 'info',     // Biru
                        'absent' => 'danger',   // Merah
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'present' => 'Hadir',
                        'sick' => 'Sakit',
                        'leave' => 'Izin',
                        'absent' => 'Alpa',
                        default => ucfirst($state),
                    })
                    ->searchable(),

                // UX HACK: Menggunakan format "H:i" agar tabel tidak sesak dengan teks tanggal yang berulang
                TextColumn::make('clock_in')
                    ->label('Masuk')
                    ->dateTime('H:i')
                    ->placeholder('-')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('clock_out')
                    ->label('Keluar')
                    ->dateTime('H:i')
                    ->placeholder('-')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Sistem Entry')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filter berdasarkan Karyawan
                SelectFilter::make('employee_id')
                    ->label('Filter Karyawan')
                    ->relationship('employee', 'full_name')
                    ->searchable(),

                // Filter berdasarkan Status (Membantu HRD mencari siapa saja yang sakit bulan ini)
                SelectFilter::make('status')
                    ->label('Filter Status')
                    ->options([
                        'present' => 'Hadir',
                        'sick' => 'Sakit',
                        'leave' => 'Izin',
                        'absent' => 'Alpa',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
                    ->icon(Heroicon::OutlinedEllipsisVertical),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Data kehadiran kosong')
            ->emptyStateDescription('Belum ada data absensi karyawan yang terekam pada sistem.')
            ->emptyStateIcon(Heroicon::OutlinedClipboardDocumentCheck);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAttendances::route('/'),
        ];
    }
}