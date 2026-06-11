<?php

namespace App\Filament\Resources\Payrolls\Schemas;

use App\Models\AllowanceType;
use App\Models\Attendance;
use App\Models\DeductionType;
use App\Models\Employee;
use App\Models\Overtime;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;

class PayrollForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // --- KARTU 1: Informasi Karyawan & Periode ---
                Section::make('Informasi Karyawan & Periode')
                    ->description('Pilih karyawan dan tentukan periode bulan penggajian.')
                    ->icon(Heroicon::OutlinedCalendarDays)
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('employee_id')
                                ->label('Nama Karyawan')
                                ->relationship('employee', 'full_name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->columnSpanFull()
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                    self::calculatePayrollAutomations($get, $set, $state);
                                }),

                            Select::make('period_month')
                                ->label('Periode Bulan')
                                ->options([
                                    1 => 'Januari',
                                    2 => 'Februari',
                                    3 => 'Maret',
                                    4 => 'April',
                                    5 => 'Mei',
                                    6 => 'Juni',
                                    7 => 'Juli',
                                    8 => 'Agustus',
                                    9 => 'September',
                                    10 => 'Oktober',
                                    11 => 'November',
                                    12 => 'Desember',
                                ])
                                ->required()
                                ->native(false)
                                ->default((int) now()->month)
                                ->live()
                                ->columnSpan(2)
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    // 🌟 Memicu kalkulasi ulang menyeluruh saat bulan diubah
                                    self::calculatePayrollAutomations($get, $set, $get('employee_id'));
                                }),

                            TextInput::make('period_year')
                                ->label('Tahun')
                                ->required()
                                ->numeric()
                                ->default((int) now()->year)
                                ->minValue(2020)
                                ->maxValue(2099)
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    // 🌟 Memicu kalkulasi ulang menyeluruh saat tahun diubah
                                    self::calculatePayrollAutomations($get, $set, $get('employee_id'));
                                }),
                        ]),
                    ]),

                // --- KARTU 2: Ringkasan Total (Finansial) ---
                Section::make('Ringkasan Finansial')
                    ->description('Total kumulatif pendapatan dan potongan.')
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('total_base_salary')
                                ->label('Gaji Pokok')
                                ->required()
                                ->numeric()
                                ->prefix('Rp')
                                ->default(0)
                                ->readOnly(),

                            TextInput::make('total_allowance')
                                ->label('Total Tunjangan')
                                ->required()
                                ->numeric()
                                ->prefix('Rp')
                                ->default(0)
                                ->readOnly(),

                            TextInput::make('total_overtime')
                                ->label('Total Lembur')
                                ->required()
                                ->numeric()
                                ->prefix('Rp')
                                ->default(0)
                                ->readOnly(),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('total_deduction')
                                ->label('Total Potongan')
                                ->required()
                                ->numeric()
                                ->prefix('Rp')
                                ->default(0)
                                ->extraInputAttributes(['class' => 'text-danger-600'])
                                ->readOnly(),

                            TextInput::make('net_salary')
                                ->label('Gaji Bersih (Take Home Pay)')
                                ->required()
                                ->numeric()
                                ->prefix('Rp')
                                ->default(0)
                                ->extraInputAttributes(['style' => 'font-weight: bold; font-size: 1.1em;'])
                                ->readOnly(),
                        ]),
                    ]),

                // --- KARTU 3: Repeater Rincian Gaji ---
                Section::make('Rincian Komponen Gaji')
                    ->description('Detail komponen yang membentuk total gaji di atas (otomatis tersimpan ke tabel payroll_details).')
                    ->icon(Heroicon::OutlinedQueueList)
                    ->schema([
                        Repeater::make('details')
                            ->relationship()
                            ->label('')
                            ->addActionLabel('Tambah Komponen Manual')
                            ->schema([
                                Select::make('component_name')
                                    ->label('Nama Komponen')
                                    ->required()
                                    ->searchable()
                                    ->columnSpanFull()
                                    ->preload()
                                    ->options(function (Get $get, ?string $state) {
                                        $type = $get('type');

                                        if ($type === 'allowance') {
                                            return AllowanceType::pluck('name', 'name')->toArray();
                                        }

                                        if ($type === 'deduction') {
                                            $deductions = DeductionType::pluck('name', 'name')->toArray();

                                            if ($state && str_contains($state, 'PPh 21')) {
                                                $deductions[$state] = $state;
                                            }

                                            if ($state && str_contains($state, 'Mangkir Kerja')) {
                                                $deductions[$state] = $state;
                                            }

                                            return $deductions;
                                        }

                                        if ($type === 'overtime') {
                                            return ['Uang Lembur' => 'Uang Lembur'];
                                        }

                                        if ($state) {
                                            return [$state => $state];
                                        }

                                        return [];
                                    })
                                    ->placeholder(fn(Get $get) => $get('type') ? 'Pilih Komponen...' : 'Pilih Sifatnya Dulu!'),

                                Grid::make(2)->schema([
                                    Select::make('type')
                                        ->label('Sifat Komponen')
                                        ->options([
                                            'allowance' => 'Tunjangan (+)',
                                            'overtime' => 'Lembur (+)',
                                            'deduction' => 'Potongan (-)',
                                        ])
                                        ->required()
                                        ->native(false)
                                        ->live()
                                        ->afterStateUpdated(fn(Set $set) => $set('component_name', null)),

                                    TextInput::make('amount')
                                        ->label('Nominal Uang')
                                        ->required()
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Get $get, Set $set) {
                                            self::recalculateTotalsFromRepeater($get, $set);
                                        }),
                                ])
                            ])
                            ->collapsible()
                            ->defaultItems(0)
                            ->reorderableWithButtons()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::recalculateTotalsFromRepeater($get, $set);
                            }),
                    ]),

                // --- KARTU 4: Persetujuan / Approval ---
                Section::make('Status & Persetujuan')
                    ->description('Tentukan status dokumen penggajian ini.')
                    ->icon(Heroicon::OutlinedCheckBadge)
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('status')
                                ->label('Status Gaji')
                                ->options([
                                    'draft' => 'Draft (Belum Selesai)',
                                    'approved' => 'Disetujui (Siap Dibayar)',
                                    'paid' => 'Telah Dibayar (Paid)',
                                ])
                                ->required()
                                ->default('draft')
                                ->native(false),

                            Select::make('approved_by_user_id')
                                ->label('Disetujui Oleh (Pimpinan)')
                                ->relationship('approver', 'name')
                                ->searchable()
                                ->preload()
                                ->helperText('Biarkan kosong jika masih dalam tahap Draft.'),
                        ]),
                    ]),
            ]);
    }

    /**
     * FUNGSI OTOMATISASI UTAMA (Mendukung Perubahan Bulan & Tahun Secara Live)
     */
    protected static function calculatePayrollAutomations(Get $get, Set $set, ?string $employeeId): void
    {
        // Jika karyawan kosong, reset semua field ringkasan dan rincian
        if (blank($employeeId)) {
            $set('total_base_salary', 0);
            $set('total_allowance', 0);
            $set('total_overtime', 0);
            $set('total_deduction', 0);
            $set('net_salary', 0);
            $set('details', []);
            return;
        }

        // 🌟 Memastikan nilai bulan dan tahun diambil secara real-time dari form state
        $month = $get('period_month') ?? now()->month;
        $year = $get('period_year') ?? now()->year;

        $startDate = Carbon::create($year, $month, 16)->startOfDay();
        $endDate = $startDate->copy()->addMonth()->setDay(15)->endOfDay();

        $employee = Employee::with(['position.allowances.allowanceType', 'position.deductions.deductionType'])
            ->find($employeeId);

        if (!$employee || !$employee->position) {
            return;
        }

        $position = $employee->position;
        $baseSalary = (float) $position->base_salary;
        $set('total_base_salary', $baseSalary);

        $detailsItems = [];
        $totalAllowance = 0;
        $totalDeduction = 0;
        $totalOvertime = 0;

        $standardWorkingDays = 22;

        // 🌟 Mengambil data kehadiran dinamis berdasarkan karyawan, bulan, dan tahun terpilih
        $actualAttendanceDays = Attendance::where('employee_id', $employeeId)
            ->where('status', 'present')
            ->whereBetween('date', [$startDate, $endDate])
            ->count();

        // 1. Tunjangan
        foreach ($position->allowances as $posAllowance) {
            $rawAmount = (float) $posAllowance->amount;
            $allowanceAmount = ($actualAttendanceDays === 0) ? 0 : $rawAmount;

            if ($allowanceAmount > 0) {
                $totalAllowance += $allowanceAmount;
                $detailsItems[] = [
                    'type' => 'allowance',
                    'component_name' => $posAllowance->allowanceType->name ?? 'Tunjangan Jabatan',
                    'amount' => $allowanceAmount,
                ];
            }
        }

        // 2. Potongan Jabatan
        foreach ($position->deductions as $posDeduction) {
            $deductionAmount = (float) $posDeduction->amount;
            $totalDeduction += $deductionAmount;
            $detailsItems[] = [
                'type' => 'deduction',
                'component_name' => $posDeduction->deductionType->name ?? 'Potongan Jabatan',
                'amount' => $deductionAmount,
            ];
        }

        // 3. Lembur (Overtime)
        $approvedOvertimeHours = Overtime::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereBetween('date', [$startDate, $endDate]) // 🌟 Query update
            ->sum('duration_hours');

        $totalOvertime = (float) Overtime::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereBetween('date', [$startDate, $endDate]) // 🌟 Query update
            ->sum('overtime_pay');

        if ($totalOvertime > 0) {
            $detailsItems[] = [
                'type' => 'overtime',
                'component_name' => 'Uang Lembur (' . round($approvedOvertimeHours, 2) . ' Jam)',
                'amount' => $totalOvertime,
            ];
        }

        // 4. Potongan Mangkir
        if ($actualAttendanceDays < $standardWorkingDays) {
            $missingDays = $standardWorkingDays - $actualAttendanceDays;
            $hoursPerDay = 10;
            $totalMissingHours = $missingDays * $hoursPerDay;

            $attendanceDeductionAmount = ($actualAttendanceDays === 0)
                ? $baseSalary
                : round($totalMissingHours * ($baseSalary / ($standardWorkingDays * $hoursPerDay)));

            if ($attendanceDeductionAmount > 0) {
                $totalDeduction += $attendanceDeductionAmount;
                $detailsItems[] = [
                    'type' => 'deduction',
                    'component_name' => "Potongan Mangkir ({$missingDays} Hari / {$totalMissingHours} Jam)",
                    'amount' => $attendanceDeductionAmount,
                ];
            }
        }

        // 5. Hitung PPh 21 TER Kategori A Otomatis
        $allowedPtkpA = ['TK/0', 'TK/1', 'K/0'];
        if ($employee->status_ptkp && in_array(strtoupper($employee->status_ptkp), $allowedPtkpA)) {
            $grossSalary = $baseSalary + $totalAllowance + $totalOvertime;
            $pph21Rate = self::getTerARate($grossSalary);
            $pph21Amount = round($grossSalary * $pph21Rate);

            if ($pph21Amount > 0) {
                $totalDeduction += $pph21Amount;
                $detailsItems[] = [
                    'type' => 'deduction',
                    'component_name' => 'PPh 21 (TER A ' . ($pph21Rate * 100) . '%)',
                    'amount' => $pph21Amount,
                ];
            }
        }

        // Simpan seluruh data yang berhasil dikalkulasi ulang ke state form Filament
        $set('details', $detailsItems);
        $set('total_allowance', $totalAllowance);
        $set('total_overtime', $totalOvertime);
        $set('total_deduction', $totalDeduction);
        $set('net_salary', max(0, ($baseSalary + $totalAllowance + $totalOvertime) - $totalDeduction));
    }

    /**
     * FUNGSI REKALKULASI REPEATER
     */
    protected static function recalculateTotalsFromRepeater(Get $get, Set $set): void
    {
        $baseSalary = (float) ($get('total_base_salary') ?? 0);
        $repeaterItems = $get('details') ?? [];

        $totalAllowance = 0;
        $totalDeduction = 0;
        $totalOvertime = 0;

        foreach ($repeaterItems as $item) {
            $amount = (float) ($item['amount'] ?? 0);
            $type = $item['type'] ?? '';

            if ($type === 'allowance') {
                $totalAllowance += $amount;
            } elseif ($type === 'deduction') {
                $totalDeduction += $amount;
            } elseif ($type === 'overtime') {
                $totalOvertime += $amount;
            }
        }

        $set('total_allowance', $totalAllowance);
        $set('total_overtime', $totalOvertime);
        $set('total_deduction', $totalDeduction);

        $netSalary = ($baseSalary + $totalAllowance + $totalOvertime) - $totalDeduction;
        $set('net_salary', max(0, $netSalary));
    }

    /**
     * HELPER: Fungsi tabel logika TER Kategori A berdasarkan Penghasilan Bruto
     */
    protected static function getTerARate(float $gross): float
    {
        if ($gross <= 5400000)
            return 0.0;
        if ($gross <= 5650000)
            return 0.0025;
        if ($gross <= 5950000)
            return 0.005;
        if ($gross <= 6300000)
            return 0.0075;
        if ($gross <= 6750000)
            return 0.01;
        if ($gross <= 7500000)
            return 0.0125;
        if ($gross <= 8550000)
            return 0.015;
        if ($gross <= 9650000)
            return 0.0175;
        if ($gross <= 10050000)
            return 0.02;
        if ($gross <= 10350000)
            return 0.0225;
        if ($gross <= 10700000)
            return 0.025;
        if ($gross <= 11050000)
            return 0.03;
        if ($gross <= 11600000)
            return 0.035;
        if ($gross <= 12500000)
            return 0.04;
        if ($gross <= 13750000)
            return 0.05;
        if ($gross <= 15100000)
            return 0.06;
        if ($gross <= 16950000)
            return 0.07;
        if ($gross <= 19750000)
            return 0.08;
        if ($gross <= 24150000)
            return 0.09;
        if ($gross <= 26450000)
            return 0.10;
        if ($gross <= 28000000)
            return 0.11;
        if ($gross <= 30050000)
            return 0.12;
        if ($gross <= 32400000)
            return 0.13;
        if ($gross <= 35400000)
            return 0.14;
        if ($gross <= 39100000)
            return 0.15;
        if ($gross <= 43850000)
            return 0.16;
        if ($gross <= 47800000)
            return 0.17;
        if ($gross <= 51400000)
            return 0.18;
        if ($gross <= 56300000)
            return 0.19;
        if ($gross <= 62200000)
            return 0.20;
        if ($gross <= 68600000)
            return 0.21;
        if ($gross <= 77500000)
            return 0.22;
        if ($gross <= 89000000)
            return 0.23;

        return 0.24; // Default batas atas jika melebihi tabel di atas (bisa disesuaikan)
    }
}
