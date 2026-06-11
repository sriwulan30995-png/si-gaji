<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use App\Models\Attendance;
use App\Models\Employee;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ManageAttendances extends ManageRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clock_in')
                ->label('Clock In (Masuk)')
                ->icon(Heroicon::OutlinedArrowRightEndOnRectangle)
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Kelayakan Masuk Kerja')
                ->modalDescription('Apakah Anda ingin melakukan absensi masuk sekarang?')
                ->action(function () {
                    $employeeId = Employee::where('user_id', Auth::id())->value('id');

                    if (!$employeeId) {
                        Notification::make()->title('Profil Karyawan tidak ditemukan!')->danger()->send();
                        return;
                    }

                    // Cek apakah hari ini sudah melakukan Clock In
                    $todayAttendance = Attendance::where('employee_id', $employeeId)
                        ->where('date', now()->toDateString())
                        ->first();

                    if ($todayAttendance) {
                        Notification::make()->title('Anda sudah melakukan absensi masuk hari ini.')->warning()->send();
                        return;
                    }

                    // Buat data absensi baru
                    Attendance::create([
                        'employee_id' => $employeeId,
                        'date' => now()->toDateString(),
                        'status' => 'present',
                        'clock_in' => now(),
                    ]);

                    Notification::make()->title('Berhasil Absen Masuk! Semangat Kerja.')->success()->send();
                })
                ->visible(fn() => Auth::user()->hasRole('Karyawan')), // Hanya muncul untuk Karyawan

            // 2. TOMBOL CLOCK OUT
            Action::make('clock_out')
                ->label('Clock Out (Pulang)')
                ->icon(Heroicon::OutlinedArrowLeftStartOnRectangle)
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Pulang Kerja')
                ->modalDescription('Apakah Anda yakin ingin mengakhiri jam kerja hari ini?')
                ->action(function () {
                    $employeeId = Employee::where('user_id', Auth::id())->value('id');

                    if (!$employeeId) {
                        return;
                    }

                    // Cari absensi hari ini yang belum diisi clock_out-nya
                    $attendance = Attendance::where('employee_id', $employeeId)
                        ->where('date', now()->toDateString())
                        ->whereNull('clock_out')
                        ->first();

                    if (!$attendance) {
                        Notification::make()->title('Anda belum Clock In hari ini atau sudah melakukan Clock Out.')->danger()->send();
                        return;
                    }

                    // Update jam pulang otomatis
                    $attendance->update([
                        'clock_out' => now(),
                    ]);

                    Notification::make()->title('Berhasil Absen Pulang! Hati-hati di jalan.')->success()->send();
                })
                ->visible(fn() => Auth::user()->hasRole('Karyawan')),
            CreateAction::make()
                ->label('Tambah Absensi Manual')
                ->visible(fn() => !Auth::user()->hasRole('Karyawan')),
        ];
    }
}