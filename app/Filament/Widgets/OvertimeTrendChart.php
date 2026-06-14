<?php

namespace App\Filament\Widgets;

use App\Models\Overtime;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class OvertimeTrendChart extends ChartWidget
{
    protected ?string $heading = 'Tren Jam Lembur Karyawan';
    protected static ?int $sort = 3;

    // Default filter adalah tahun ini
    public ?string $filter = 'this_year';

    // Membuat dropdown filter di pojok kanan chart
    protected function getFilters(): ?array
    {
        return [
            'this_year' => 'Tahun Ini',
            'last_year' => 'Tahun Lalu',
        ];
    }

    public static function canView(): bool
    {
        return Auth::user()->hasRole(['Administrator', 'Pimpinan']);
    }

    protected function getData(): array
    {
        // Tentukan tahun berdasarkan filter yang dipilih
        $year = $this->filter === 'last_year' ? Carbon::now()->subYear()->year : Carbon::now()->year;

        // Mengambil data lembur yang sudah disetujui, dikelompokkan per bulan
        $overtimes = Overtime::whereYear('date', $year)
            ->where('status', 'approved')
            ->selectRaw('MONTH(date) as month, SUM(duration_hours) as total_hours')
            ->groupBy('month')
            ->pluck('total_hours', 'month')
            ->toArray();

        $data = [];
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];

        for ($i = 1; $i <= 12; $i++) {
            $data[] = $overtimes[$i] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Jam Lembur',
                    'data' => $data,
                    'borderColor' => '#F59E0B', // Warna oranye (Tailwind Amber)
                    'fill' => 'start', // Memberikan efek blok warna transparan di bawah garis
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
