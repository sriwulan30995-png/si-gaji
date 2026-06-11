<?php

namespace App\Filament\Resources\Overtimes\Pages;

use App\Filament\Resources\Overtimes\OvertimeResource;
use App\Models\Employee;
use App\Models\Overtime;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ManageOvertimes extends ManageRecords
{
    protected static string $resource = OvertimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('overtime_clock_in')
                ->label('Mulai Lembur')
                ->icon('heroicon-o-play')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Mulai Lembur')
                ->modalDescription('Apakah Anda ingin memulai jam kerja lembur Anda sekarang?')
                ->action(function () {
                    $employeeId = Employee::where('user_id', Auth::id())->value('id');

                    if (!$employeeId) {
                        Notification::make()->title('Profil Karyawan tidak ditemukan!')->danger()->send();
                        return;
                    }

                    // Cek apakah ada lemburan hari ini yang statusnya masih berjalan (belum clock out)
                    $activeOvertime = Overtime::where('employee_id', $employeeId)
                        ->where('date', now()->toDateString())
                        ->whereNull('clock_out')
                        ->first();

                    if ($activeOvertime) {
                        Notification::make()->title('Anda masih memiliki sesi lembur yang sedang berjalan.')->warning()->send();
                        return;
                    }

                    // Buat data pengajuan lembur baru dengan status pending
                    Overtime::create([
                        'employee_id' => $employeeId,
                        'date' => now()->toDateString(),
                        'clock_in' => now(),
                        'status' => 'pending',
                    ]);

                    Notification::make()->title('Sesi lembur berhasil dimulai! Selamat bekerja.')->success()->send();
                })
                ->visible(fn() => Auth::user()->hasRole('Karyawan')),

            // 2. TOMBOL SELESAI LEMBUR (CLOCK OUT) + LOGIKA HITUNG RUPIAH & ISTIRAHAT
            Action::make('overtime_clock_out')
                ->label('Selesai Lembur')
                ->icon('heroicon-o-stop')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Selesai Lembur')
                ->modalDescription('Apakah Anda yakin ingin mengakhiri sesi lembur saat ini? Pendapatan dan durasi (potong 2 jam istirahat jika memenuhi syarat) akan dihitung otomatis.')
                ->action(function () {
                    $employeeId = Employee::where('user_id', Auth::id())->value('id');

                    if (!$employeeId) {
                        return;
                    }

                    // Cari data lembur hari ini yang belum melakukan clock out
                    $overtime = Overtime::where('employee_id', $employeeId)
                        ->where('date', now()->toDateString())
                        ->whereNull('clock_out')
                        ->latest()
                        ->first();

                    if (!$overtime) {
                        Notification::make()->title('Anda belum memulai lembur hari ini atau sudah melakukan Selesai Lembur.')->danger()->send();
                        return;
                    }

                    $date = Carbon::parse($overtime->date);
                    $startTime = Carbon::parse($overtime->clock_in);
                    $endTime = now(); // Waktu sekarang saat klik tombol
        
                    // --- [LOGIKA 1]: HITUNG DURASI BERSIH & POTONGAN ISTIRAHAT ---
                    $totalMinutes = $startTime->diffInMinutes($endTime);

                    $potonganMenit = 0;
                    if ($totalMinutes > 120) {
                        $potonganMenit = 120; // Potong 2 jam jika kerja lembur > 2 jam
                    }

                    $cleanMinutes = max(0, $totalMinutes - $potonganMenit);
                    $durationHours = round($cleanMinutes / 60, 2);

                    // --- [LOGIKA 2]: HITUNG NOMINAL UANG LEMBUR ---
                    $employee = Employee::with('position')->find($employeeId);
                    $hourlyRate = $employee?->position?->hourly_overtime_rate ?? 0;

                    $totalPay = 0;
                    $isWeekend = $date->isWeekend();

                    if (!$isWeekend) {
                        // --- HARI BIASA ---
                        $maxTime = $startTime->copy()->setTime(22, 0, 0);
                        if ($endTime->gt($maxTime)) {
                            $endTime = $maxTime; // Batasi paksa ke jam 22:00
                        }

                        if ($startTime->lt($endTime)) {
                            $validMinutes = $startTime->diffInMinutes($endTime);
                            $validMinutes = max(0, $validMinutes - $potonganMenit);
                            $totalPay = ($validMinutes / 60) * ($hourlyRate * 1.5);
                        }
                    } else {
                        // --- HARI LIBUR / TANGGAL MERAH ---
                        $limitTime = $startTime->copy()->setTime(18, 30, 0);

                        // Zona 1: 08.00 - 18.30 (Rate x 2)
                        $zona1Start = $startTime->copy()->max($startTime->copy()->setTime(8, 0, 0));
                        $zona1End = $endTime->copy()->min($limitTime);
                        $minutesZona1 = 0;

                        if ($zona1Start->lt($zona1End)) {
                            $minutesZona1 = $zona1Start->diffInMinutes($zona1End);
                        }

                        // Zona 2: 18.30 - 00.00 (Rate x 2.5)
                        $zona2Start = $startTime->copy()->max($limitTime);
                        $zona2End = $endTime->copy()->min($endTime->copy()->endOfDay());
                        $minutesZona2 = 0;

                        if ($zona2Start->lt($zona2End)) {
                            $minutesZona2 = $zona2Start->diffInMinutes($zona2End);
                        }

                        // Distribusi potongan 2 jam (prioritas potong Zona 1 dulu)
                        if ($potonganMenit > 0) {
                            if ($minutesZona1 >= $potonganMenit) {
                                $minutesZona1 -= $potonganMenit;
                            } else {
                                $sisaPotongan = $potonganMenit - $minutesZona1;
                                $minutesZona1 = 0;
                                $minutesZona2 = max(0, $minutesZona2 - $sisaPotongan);
                            }
                        }

                        $totalPay += ($minutesZona1 / 60) * ($hourlyRate * 2);
                        $totalPay += ($minutesZona2 / 60) * ($hourlyRate * 2.5);
                    }

                    // --- [LOGIKA 3]: UPDATE DATABASE ---
                    $overtime->update([
                        'clock_out' => now(), // Catat waktu asli clock out
                        'duration_hours' => $durationHours,
                        'overtime_pay' => round($totalPay, 0),
                    ]);

                    Notification::make()
                        ->title("Lembur selesai! Durasi bersih: {$durationHours} Jam. Estimasi: Rp " . number_format($totalPay, 0, ',', '.'))
                        ->success()
                        ->send();
                })
                ->visible(fn() => Auth::user()->hasRole('Karyawan')),

            // 3. TOMBOL TAMBAH MANUAL (BAWAAN FILAMENT)
            CreateAction::make()
                ->label('Tambah Lemburan Manual')
                ->visible(fn() => !Auth::user()->hasRole('Karyawan')),
        ];
    }
}