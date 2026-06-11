<?php

namespace App\Filament\Resources\Payrolls\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;

class PayrollInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Karyawan')
                    ->icon(Heroicon::OutlinedUserCircle)
                    ->schema([
                        Grid::make(3)->schema([
                            // UX HACK: Ambil nama dari relasi, bukan angka ID
                            TextEntry::make('employee.full_name')
                                ->label('Nama Karyawan')
                                ->weight(FontWeight::Bold) 
                                ->icon(Heroicon::OutlinedUser),

                            // UX HACK: Konversi angka bulan (1-12) menjadi nama bulan (Januari-Desember)
                            TextEntry::make('period_month')
                                ->label('Periode Bulan')
                                ->formatStateUsing(fn(int $state): string => match ($state) {
                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                                    default => 'Tidak diketahui',
                                })
                                ->weight(FontWeight::SemiBold),

                            TextEntry::make('period_year')
                                ->label('Tahun')
                                ->weight(FontWeight::SemiBold),
                        ]),
                    ]),

                // --- KARTU 2: Rincian Finansial (Slip Gaji Virtual) ---
                Section::make('Rincian Gaji (Finansial)')
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->schema([
                        Grid::make(2)->schema([
                            // Kolom Kiri: PENDAPATAN
                            Grid::make(1)
                                ->schema([
                                    TextEntry::make('total_base_salary')
                                        ->label('Gaji Pokok')
                                        ->money('IDR', locale: 'id'),

                                    TextEntry::make('total_allowance')
                                        ->label('Total Tunjangan')
                                        ->money('IDR', locale: 'id')
                                        ->color('success'), // Hijau untuk tambahan pendapatan

                                    TextEntry::make('total_overtime')
                                        ->label('Total Uang Lembur')
                                        ->money('IDR', locale: 'id')
                                        ->color('success'),
                                ])->columnSpan(1),

                            // Kolom Kanan: POTONGAN & HASIL BERSIH
                            Grid::make(1)
                                ->schema([
                                    TextEntry::make('total_deduction')
                                        ->label('Total Potongan (BPJS, Pajak, dll)')
                                        ->money('IDR', locale: 'id')
                                        ->color('danger'), // Merah untuk pengurangan

                                    // Paling Penting: TAKE HOME PAY
                                    TextEntry::make('net_salary')
                                        ->label('Gaji Bersih (Take Home Pay)')
                                        ->money('IDR', locale: 'id')
                                        ->weight(FontWeight::ExtraBold) // Sangat tebal
                                        ->color('primary') // Warna utama (biasanya biru/kuning tergantung tema)
                                        ->icon(Heroicon::Banknotes),
                                ])->columnSpan(1),
                        ]),
                    ]),

                // --- KARTU 3: Status & Jejak Audit ---
                Section::make('Status Dokumen')
                    ->icon(Heroicon::OutlinedCheckBadge)
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('status')
                                ->label('Status Pembayaran')
                                ->badge()
                                ->color(fn(string $state): string => match ($state) {
                                    'approved' => 'success',
                                    'draft' => 'warning',
                                    'paid' => 'info',
                                    default => 'gray',
                                })
                                ->formatStateUsing(fn(string $state): string => ucfirst($state)),

                            TextEntry::make('approver.name')
                                ->label('Disetujui Oleh')
                                ->icon(Heroicon::OutlinedCheckCircle)
                                ->placeholder('Menunggu Persetujuan'),

                            TextEntry::make('created_at')
                                ->label('Tanggal Dibuat')
                                ->dateTime('d F Y, H:i')
                                ->color('gray'),
                        ]),
                    ]),
            ]);
    }
}
