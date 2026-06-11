<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\Overtime;
use App\Models\Payroll;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverview extends StatsOverviewWidget
{
    // Opsional: Atur urutan kemunculan widget di dashboard
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Dapatkan bulan dan tahun saat ini
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // 1. Query: Menghitung jumlah karyawan yang is_active = true
        $activeEmployeesCount = Employee::where('is_active', true)->count();

        // 2. Query: Menjumlahkan net_salary pada payroll bulan ini
        $totalPayrollThisMonth = Payroll::where('period_month', $currentMonth)
            ->where('period_year', $currentYear)
            ->sum('net_salary');
        // Format ke Rupiah
        $formattedPayroll = 'Rp ' . number_format($totalPayrollThisMonth, 0, ',', '.');

        // 3. Query: Menghitung lembur yang butuh persetujuan ('pending')
        $pendingOvertimeCount = Overtime::where('status', 'pending')->count();

        return [
            // Stat 1: Karyawan Aktif
            Stat::make('Total Karyawan Aktif', $activeEmployeesCount)
                ->description('Jumlah karyawan aktif di sistem')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            // Stat 2: Total Pengeluaran Gaji
            Stat::make('Pengeluaran Gaji Bulan Ini', $formattedPayroll)
                ->description('Periode ' . Carbon::now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            // Stat 3: Lembur Pending
            Stat::make('Lembur Menunggu Persetujuan', $pendingOvertimeCount)
                ->description($pendingOvertimeCount > 0 ? 'Perlu tindakan approval' : 'Semua lembur telah diproses')
                ->descriptionIcon('heroicon-m-clock')
                // Warnanya otomatis berubah: peringatan (kuning/merah) jika ada pending, sukses (hijau) jika kosong
                ->color($pendingOvertimeCount > 0 ? 'warning' : 'success'),
        ];
    }
}
