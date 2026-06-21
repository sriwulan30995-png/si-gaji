<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\Overtime;
use App\Models\Payroll;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class ManagementStats extends StatsOverviewWidget
{
    public static function canView(): bool
    {
        return Auth::user()->hasRole('Pimpinan');
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Total Karyawan', Employee::where('is_active', true)->count())
                ->description('Karyawan aktif')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
            Stat::make('Approval Penggajian', Payroll::where('status', 'paid')->count())
                ->description('Menunggu Approval')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('warning'),
            Stat::make('Total Pengeluaran Gaji', 'Rp ' . number_format(Payroll::sum('net_salary'), 0, ',', '.'))
                ->description('Total kumulatif')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
