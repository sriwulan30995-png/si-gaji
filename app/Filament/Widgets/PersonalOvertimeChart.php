<?php

namespace App\Filament\Widgets;

use App\Models\Overtime;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PersonalOvertimeChart extends ChartWidget
{
    protected ?string $heading = 'Riwayat Jam Lembur Saya (6 Bulan Terakhir)';

    public static function canView(): bool
    {
        return Auth::user()->hasRole('Karyawan');
    }

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $employeeId = Auth::user()->employee->id ?? 0;

        // Query manual menggunakan Eloquent & Query Builder
        // Kita grouping berdasarkan bulan dan tahun
        $data = Overtime::query()
            ->where('employee_id', $employeeId)
            ->where('date', '>=', now()->subMonths(6))
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as month, SUM(duration_hours) as total")
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Jam Lembur',
                    'data' => $data->pluck('total')->toArray(), // Mengambil angka hasil SUM
                    'borderColor' => '#F59E0B',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
                    'fill' => true,
                ]
            ],
            // Mengubah format YYYY-MM menjadi M Y (contoh: Feb 2026)
            'labels' => $data->map(fn($item) => Carbon::parse($item->month . '-01')->format('M Y'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
