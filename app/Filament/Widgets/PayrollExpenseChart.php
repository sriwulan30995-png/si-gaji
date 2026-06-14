<?php

namespace App\Filament\Widgets;

use App\Models\Payroll;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class PayrollExpenseChart extends ChartWidget
{
    protected ?string $heading = 'Grafik Pengeluaran Gaji (Tahun Ini)';
    protected static ?int $sort = 2; // Tampil setelah StatsOverview
    public static function canView(): bool
    {
        return Auth::user()->hasRole(['Administrator', 'Pimpinan']);
    }
    protected function getData(): array
    {
        $currentYear = Carbon::now()->year;

        // Mengambil dan menjumlahkan net_salary berdasarkan period_month
        $payrolls = Payroll::where('period_year', $currentYear)
            ->where('status', 'approved') // Opsional: hanya hitung yang sudah disetujui
            ->selectRaw('period_month, SUM(net_salary) as total')
            ->groupBy('period_month')
            ->pluck('total', 'period_month')
            ->toArray();

        $data = [];
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];

        // Looping untuk memastikan 12 bulan terisi (isi 0 jika bulan tersebut belum ada data)
        for ($i = 1; $i <= 12; $i++) {
            $data[] = $payrolls[$i] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Gaji Bersih (Rp)',
                    'data' => $data,
                    'backgroundColor' => '#10B981', // Warna hijau (Tailwind Emerald)
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
