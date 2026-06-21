<?php

namespace App\Filament\Resources\Payrolls\Tables;

use App\Models\Payroll;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PayrollsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // UX: Urutkan dari data terbaru terlebih dahulu (berdasarkan tahun dan bulan)
            ->modifyQueryUsing(function (Builder $query) {
                // Cek apakah user yang sedang login memiliki peran 'karyawan'
                if (Auth::user()->hasRole('Karyawan')) {
                    // Filter relasi employee agar user_id cocok dengan ID user yang login
                    $query->whereHas('employee', function (Builder $query) {
                        $query->where('user_id', Auth::id());
                    });
                }
            })
            ->defaultSort('created_at', 'desc')
            ->columns([
                // UX HACK: Menampilkan Nama Karyawan, bukan ID angka
                TextColumn::make('employee.full_name')
                    ->label('Nama Karyawan')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->icon(Heroicon::OutlinedUserCircle),

                // UX HACK: Menggabungkan Bulan dan Tahun agar lebih hemat ruang tabel
                TextColumn::make('period_month')
                    ->label('Periode')
                    ->formatStateUsing(fn(int $state): string => match ($state) {
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                        default => '-',
                    })
                    ->description(fn(Payroll $record): string => 'Tahun: ' . $record->period_year)
                    ->sortable(),

                // Kolom Tahun disembunyikan karena sudah digabung ke deskripsi bulan di atas
                TextColumn::make('period_year')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total_base_salary')
                    ->label('Gaji Pokok')
                    ->money('IDR', locale: 'id')
                    ->alignEnd() // Wajib: Rata kanan untuk angka keuangan
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Disembunyikan agar tabel tidak penuh

                TextColumn::make('total_allowance')
                    ->label('Tunjangan')
                    ->money('IDR', locale: 'id')
                    ->alignEnd()
                    ->color('success')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total_deduction')
                    ->label('Potongan')
                    ->money('IDR', locale: 'id')
                    ->alignEnd()
                    ->color('danger')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // BINTANG UTAMA: Gaji Bersih harus paling menonjol
                TextColumn::make('net_salary')
                    ->label('Gaji Bersih (THP)')
                    ->money('IDR', locale: 'id')
                    ->alignEnd()
                    ->weight(FontWeight::ExtraBold)
                    ->color('primary')
                    ->sortable(),

                // UX HACK: Badge visual untuk membedakan status dengan cepat
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'paid' => 'info',         // Biru: Telah Dibayar
                        'approved' => 'success',  // Hijau: Disetujui Pimpinan
                        'draft' => 'warning',     // Kuning/Oranye: Masih dihitung
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'paid' => 'Telah Dibayar',
                        'approved' => 'Disetujui',
                        'draft' => 'Draft',
                        default => ucfirst($state),
                    })
                    ->searchable(),

                // UX HACK: Menampilkan Nama Penyetuju
                TextColumn::make('approver.name')
                    ->label('Disetujui Oleh')
                    ->placeholder('Belum ada')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // UX: Filter ini SANGAT PENTING untuk HRD saat mau merekap gaji per bulan
                SelectFilter::make('period_month')
                    ->label('Filter Bulan')
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
                    ]),

                SelectFilter::make('period_year')
                    ->label('Filter Tahun')
                    ->options(function () {
                        $years = [];
                        $currentYear = date('Y');
                        for ($i = 0; $i < 5; $i++) {
                            $years[$currentYear - $i] = $currentYear - $i;
                        }
                        return $years;
                    }),

                SelectFilter::make('status')
                    ->label('Filter Status')
                    ->options([
                        'draft' => 'Draft',
                        'approved' => 'Disetujui',
                        'paid' => 'Telah Dibayar',
                    ]),
            ])
            ->recordActions([
                // 1. Tombol Mark as Paid (Administrator, saat status 'draft')
                Action::make('mark_as_paid')
                    ->label('Mark as Paid')
                    ->icon('heroicon-o-banknotes')
                    ->color('info')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Tandai Gaji Sudah Dibayar?')
                    ->modalDescription('Apakah Anda yakin ingin mengubah status penggajian ini menjadi Paid?')
                    ->visible(function ($record) {
                        // Sesuaikan metode pengecekan role di bawah ini dengan sistem Anda
                        // Contoh menggunakan Spatie Permission: auth()->user()->hasRole('Administrator')
                        // Contoh menggunakan kolom role murni: auth()->user()->role === 'Administrator'
                        return $record->status === 'draft' && Auth::user()->hasRole('Administrator');
                    })
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'paid',
                        ]);
                    }),

                // 2. Tombol Cetak Slip (Muncul setelah mark as paid atau approved)
                Action::make('cetak_slip')
                    ->label('Cetak Slip')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('warning')
                    ->visible(fn($record) => in_array($record->status, ['paid', 'approved']))
                    ->url(fn($record) => route('payroll.download', $record))
                    ->openUrlInNewTab(),

                // 3. Tombol Approve (Khusus Pimpinan, saat status 'paid')
                Action::make('mark_as_approve')
                    ->label('Approve Pimpinan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Approve Penggajian?')
                    ->modalDescription('Apakah Anda yakin menyetujui penggajian ini?')
                    ->visible(function ($record) {
                        // Sama seperti di atas, sesuaikan pengecekan role-nya
                        return $record->status === 'paid' && Auth::user()->hasRole('Pimpinan');
                    })
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'approved',
                            // Opsional: Catat ID user pimpinan yang melakukan approve ke database
                            'approved_by_user_id' => auth()->id(),
                        ]);
                    }),
                // UX: Kelompokkan action agar layar tabel lega
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                ])
                    ->icon(Heroicon::OutlinedEllipsisVertical)
                    ->tooltip('Buka Opsi'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada data Penggajian')
            ->emptyStateDescription('Data gaji akan muncul di sini setelah Anda memproses penggajian bulanan.')
            ->emptyStateIcon(Heroicon::OutlinedBanknotes);
    }
}
