<?php

namespace App\Filament\Widgets;

use App\Models\Overtime;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class OvertimeStatusChart extends ChartWidget
{
    protected ?string $heading = 'Distribusi Status Pengajuan Lembur';

    public static function canView(): bool
    {
        return Auth::user()->hasRole('Karyawan');
    }

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $employeeId = Auth::user()->employee->id ?? 0;

        // Mengambil data count berdasarkan status
        $data = Overtime::query()
            ->where('employee_id', $employeeId)
            ->selectRaw("status, count(*) as total")
            ->groupBy('status')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Status Lembur',
                    'data' => $data->pluck('total')->toArray(),
                    'backgroundColor' => [
                        '#10B981', // Hijau untuk Approved
                        '#F59E0B', // Kuning untuk Pending
                        '#EF4444', // Merah untuk Rejected
                    ],
                ]
            ],
            'labels' => $data->map(fn($item) => ucfirst($item->status))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie'; // Menggunakan tipe Pie untuk distribusi
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
