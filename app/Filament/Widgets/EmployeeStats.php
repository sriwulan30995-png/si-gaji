<?php

namespace App\Filament\Widgets;

use App\Models\Overtime;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class EmployeeStats extends StatsOverviewWidget
{
    public static function canView(): bool
    {
        return Auth::user()->hasRole('Karyawan');
    }
    protected static ?int $sort = 1;


    protected function getStats(): array
    {
        $employeeId = Auth::user()->employee->id ?? 0;

        return [
            Stat::make('Lembur Bulan Ini', Overtime::where('employee_id', $employeeId)->whereMonth('date', now()->month)->sum('duration_hours') . ' Jam')
                ->description('Total durasi lembur')
                ->color('success'),
            Stat::make('Status Pengajuan', Overtime::where('employee_id', $employeeId)->where('status', 'pending')->count())
                ->description('Lembur dalam proses')
                ->color('warning'),
            Stat::make('Total Pendapatan Lembur', 'Rp ' . number_format(
                Overtime::where('employee_id', $employeeId)
                    ->where('status', 'approved')
                    ->sum('overtime_pay'),
                0,
                ',',
                '.'
            ))
                ->description('Total pendapatan lembur disetujui')
                ->color('info'),
        ];
    }
}
