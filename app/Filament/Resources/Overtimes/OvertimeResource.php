<?php

namespace App\Filament\Resources\Overtimes;

use App\Filament\Resources\Overtimes\Pages\ManageOvertimes;
use App\Models\Employee;
use App\Models\Overtime;
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
use Filament\Forms\Components\TimePicker;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use UnitEnum;

class OvertimeResource extends Resource
{
    protected static ?string $model = Overtime::class;

    // --- Konfigurasi Navigasi (UI/UX) ---
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;
    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Clock;

    protected static ?string $navigationLabel = 'Lembur';
    protected static ?string $modelLabel = 'Data Lembur';
    protected static ?string $pluralModelLabel = 'Riwayat Lembur';

    // Grup baru khusus aktivitas harian/bulanan
    protected static string|UnitEnum|null $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 6;
    protected static ?string $slug = 'riwayat-lembur';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Lembur')
                    ->description('Catat pengajuan jam lembur karyawan.')
                    ->icon(Heroicon::OutlinedClock)
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('employee_id')
                                ->label('Nama Karyawan')
                                ->relationship('employee', 'full_name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->reactive()
                                ->afterStateUpdated(fn(Get $get, Set $set) => self::calculateDuration($get, $set))
                                ->default(function () {
                                    if (Auth::user()->hasRole('Karyawan')) {
                                        return Employee::where('user_id', Auth::id())->value('id');
                                    }
                                    return null;
                                })
                                ->disabled(fn() => Auth::user()->hasRole('Karyawan'))
                                ->dehydrated(),

                            DatePicker::make('date')
                                ->label('Tanggal Lembur')
                                ->required()
                                ->native(false)
                                ->displayFormat('d F Y')
                                ->default(now())
                                ->reactive()
                                ->afterStateUpdated(fn(Get $get, Set $set) => self::calculateDuration($get, $set))
                                ->closeOnDateSelection(),

                            // 🌟 CLOCK IN LEMBUR
                            TimePicker::make('clock_in')
                                ->label('Jam Mulai Lembur')
                                ->required()
                                ->native(false)
                                ->seconds(false)
                                ->reactive()
                                ->afterStateUpdated(fn(Get $get, Set $set) => self::calculateDuration($get, $set)),

                            // 🌟 CLOCK OUT LEMBUR
                            TimePicker::make('clock_out')
                                ->label('Jam Selesai Lembur')
                                ->required()
                                ->native(false)
                                ->seconds(false)
                                ->reactive()
                                ->afterStateUpdated(fn(Get $get, Set $set) => self::calculateDuration($get, $set)),

                            // 🌟 DURASI OTOMATIS (READ-ONLY)
                            TextInput::make('duration_hours')
                                ->label('Durasi Lembur Otomatis')
                                ->required()
                                ->numeric()
                                ->suffix(' Jam')
                                ->readOnly()
                                ->helperText(function (Get $get) {
                                    $clockIn = $get('clock_in');
                                    $clockOut = $get('clock_out');

                                    if (!$clockIn || !$clockOut) {
                                        return 'Pilih Jam Mulai dan Jam Selesai untuk menghitung durasi.';
                                    }

                                    $startTime = Carbon::parse($clockIn);
                                    $endTime = Carbon::parse($clockOut);
                                    $totalMinutes = $startTime->diffInMinutes($endTime);
                                    $totalHours = round($totalMinutes / 60, 2);

                                    if ($totalMinutes > 120) {
                                        return "Total waktu: {$totalHours} jam (Sudah dipotong 2 jam istirahat otomatis).";
                                    }

                                    return "Total waktu: {$totalHours} jam (Belum memenuhi syarat potongan istirahat 2 jam).";
                                })
                                ->dehydrated(),

                            // 🌟 TOTAL UANG LEMBUR (READ-ONLY)
                            TextInput::make('overtime_pay')
                                ->label('Estimasi Pendapatan Lembur')
                                ->required()
                                ->numeric()
                                ->prefix('Rp ')
                                ->readOnly()
                                ->helperText(function (Get $get) {
                                    $clockIn = $get('clock_in');
                                    $clockOut = $get('clock_out');
                                    $dateInput = $get('date');
                                    $employeeId = $get('employee_id');

                                    if (!$clockIn || !$clockOut || !$dateInput || !$employeeId) {
                                        return 'Rincian tarif perkalian akan muncul di sini.';
                                    }

                                    $date = Carbon::parse($dateInput);
                                    $isWeekend = $date->isWeekend();
                                    $endTime = Carbon::parse($clockOut);

                                    // Ambil info rate karyawan untuk ditampilkan di teks info
                                    $employee = Employee::with('position')->find($employeeId);
                                    $hourlyRate = $employee?->position?->hourly_overtime_rate ?? 0;
                                    $formattedRate = 'Rp ' . number_format($hourlyRate, 0, ',', '.');

                                    if (!$isWeekend) {
                                        // Logika Hari Biasa
                                        $maxTime = Carbon::parse($clockIn)->setTime(22, 0, 0);
                                        $infoTeks = "Hari Kerja: Tarif 1.5x ({$formattedRate}/jam).";

                                        if ($endTime->gt($maxTime)) {
                                            $infoTeks .= "Melebihi jam 22:00, waktu setelahnya tidak dihitung.";
                                        }
                                        return $infoTeks;
                                    } else {
                                        // Logika Hari Libur
                                        $limitTime = Carbon::parse($clockIn)->setTime(18, 30, 0);
                                        $startTime = Carbon::parse($clockIn);

                                        // Deteksi pembagian zona untuk helper text
                                        if ($endTime->lte($limitTime)) {
                                            return "Hari Libur (Zona 1): Full menggunakan tarif 2x ({$formattedRate}/jam).";
                                        } elseif ($startTime->gte($limitTime)) {
                                            return "Hari Libur (Zona 2): Full menggunakan tarif 2.5x ({$formattedRate}/jam).";
                                        } else {
                                            return "Hari Libur Campuran: Jam sebelum 18:30 (Tarif 2x) & Setelah 18:30 (Tarif 2.5x).";
                                        }
                                    }
                                })
                                ->dehydrated(),
                        ]),
                    ])->columnSpanFull(),

                Section::make('Persetujuan (Approval)')
                    ->description('Status validasi lembur oleh atasan atau HRD.')
                    ->icon(Heroicon::OutlinedCheckBadge)
                    ->visible(fn() => !Auth::user()->hasRole('Karyawan'))
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('status')
                                ->label('Status Pengajuan')
                                ->options([
                                    'pending' => 'Menunggu Persetujuan (Pending)',
                                    'approved' => 'Disetujui (Approved)',
                                    'rejected' => 'Ditolak (Rejected)',
                                ])
                                ->required()
                                ->default('pending')
                                ->native(false),

                            Select::make('approved_by_user_id')
                                ->label('Disetujui / Diproses Oleh')
                                ->relationship('approver', 'name')
                                ->searchable()
                                ->preload()
                                ->helperText('Kosongkan jika status masih pending.'),
                        ]),
                    ])->columnSpanFull(),
            ]);
    }

    /**
     * Helper: Cek Libur via API
     */
    protected static function isHoliday(Carbon $date): bool
    {
        return Cache::remember('holiday_' . $date->format('Y-m-d'), 3600, function () use ($date): bool {
            try {
                $response = Http::timeout(3)->get('https://libur.deno.dev/api');
                if ($response->successful()) {
                    $holidays = $response->json();
                    return collect($holidays)->contains(fn($h) => ($h['date'] ?? '') === $date->format('Y-m-d'));
                }
            } catch (\Exception $e) {
                Log::error('Gagal akses API libur: ' . $e->getMessage());
            }
            return $date->isWeekend();
        });
    }

    /**
     * Helper: Kalkulasi Durasi & Gaji
     */
    public static function calculateDuration(Get $get, Set $set): void
    {
        $clockIn = $get('clock_in');
        $clockOut = $get('clock_out');
        $dateInput = $get('date');
        $employeeId = $get('employee_id');

        if ($clockIn && $clockOut && $dateInput && $employeeId) {
            $date = Carbon::parse($dateInput);
            $startTime = Carbon::parse($clockIn)->max(Carbon::parse($clockIn)->setTime(8, 0, 0));
            $endTime = Carbon::parse($clockOut);

            if ($startTime->gte($endTime)) {
                $set('duration_hours', 0);
                $set('overtime_pay', 0);
                return;
            }

            // Hitung total jam
            $totalMinutes = $startTime->diffInMinutes($endTime);
            $totalHours = $totalMinutes / 60;

            // Aturan Istirahat: 1 jam tiap 4 jam kerja lembur
            $breakHours = floor($totalHours / 4);
            $paidHours = max(0, $totalHours - $breakHours);

            $set('duration_hours', round($paidHours, 2));

            // Kalkulasi Upah
            $employee = Employee::with('position')->find($employeeId);
            $hourlyRate = $employee?->position?->hourly_overtime_rate ?? 0;
            $isHoliday = self::isHoliday($date);
            $totalPay = 0;

            if (!$isHoliday) {
                // Hari Biasa: Tarif 1.5x
                $totalPay = $paidHours * ($hourlyRate * 1.5);
            } else {
                // Hari Libur: Pembagian zona (Sederhana)
                // Zona 1: Rate 2x, Zona 2 (setelah 18.30): Rate 2.5x
                $limitTime = Carbon::parse($clockIn)->setTime(18, 30, 0);

                // Distribusi jam lembur ke zona
                $hoursZona1 = min($paidHours, max(0, $startTime->diffInMinutes($endTime->min($limitTime))) / 60);
                $hoursZona2 = max(0, $paidHours - $hoursZona1);

                $totalPay = ($hoursZona1 * ($hourlyRate * 2)) + ($hoursZona2 * ($hourlyRate * 2.5));
            }

            $set('overtime_pay', round($totalPay, 0));
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                if (Auth::user()->hasRole('Karyawan')) {
                    $query->whereHas('employee', function (Builder $query) {
                        $query->where('user_id', Auth::id());
                    });
                }
            })
            ->defaultSort('created_at', 'desc')
            ->recordTitleAttribute('name')
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

                // Menampilkan Jam Mulai
                TextColumn::make('clock_in')
                    ->label('Mulai')
                    ->dateTime('H:i')
                    ->alignCenter(),

                // Menampilkan Jam Selesai
                TextColumn::make('clock_out')
                    ->label('Selesai')
                    ->dateTime('H:i')
                    ->alignCenter(),

                TextColumn::make('duration_hours')
                    ->label('Durasi')
                    ->numeric(1) // Menampilkan 1 angka di belakang koma (contoh: 2,5 Jam)
                    ->suffix(' Jam')
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => ucfirst($state))
                    ->searchable(),

                TextColumn::make('approver.name')
                    ->label('Penyetuju')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('overtime_pay')
                    ->label('Pendapatan Lembur')
                    ->money('IDR', locale: 'id') // Format otomatis jadi Rp XX.XXX
                    ->alignEnd()
                    ->sortable()
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Filter Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),

                SelectFilter::make('employee_id')
                    ->label('Filter Karyawan')
                    ->relationship('employee', 'full_name')
                    ->searchable(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])->icon(Heroicon::OutlinedEllipsisVertical),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Lembur')
                    ->schema([
                        TextEntry::make('employee.full_name')->label('Karyawan')->weight(FontWeight::Bold),
                        TextEntry::make('date')->label('Tanggal')->date('d F Y'),
                        TextEntry::make('clock_in')->label('Jam Mulai')->dateTime('H:i'),
                        TextEntry::make('clock_out')->label('Jam Selesai')->dateTime('H:i'),
                        TextEntry::make('duration_hours')->label('Total Waktu')->suffix(' Jam'),
                        TextEntry::make('overtime_pay')->label('Pendapatan Lembur')->money('IDR', locale: 'id'), // 🌟 Tambahkan ini
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'approved' => 'success',
                                'pending' => 'warning',
                                'rejected' => 'danger',
                                default => 'gray',
                            }),
                    ])->columns(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageOvertimes::route('/'),
        ];
    }
}
