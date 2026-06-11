<?php

namespace App\Filament\Resources\PayrollDetails;

use App\Filament\Resources\PayrollDetails\Pages\ManagePayrollDetails;
use App\Models\PayrollDetail;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class PayrollDetailResource extends Resource
{
    protected static ?string $model = PayrollDetail::class;
    // --- Konfigurasi Navigasi (UI/UX) ---
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;
    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::ClipboardDocumentList;

    protected static ?string $navigationLabel = 'Rincian Komponen';
    protected static ?string $modelLabel = 'Rincian Komponen Gaji';
    protected static ?string $pluralModelLabel = 'Detail Rincian Gaji';

    // Diletakkan di bawah Penggajian
    protected static string|UnitEnum|null $navigationGroup = 'Eksekusi';
    protected static ?int $navigationSort = 8;
    protected static ?string $slug = 'rincian-gaji';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Komponen')
                    ->description('Detail komponen yang menambah atau mengurangi gaji karyawan.')
                    ->icon(Heroicon::OutlinedQueueList)
                    ->schema([
                        Grid::make(2)->schema([
                            // UX HACK: Menggunakan Dropdown untuk mencari ID Penggajian
                            Select::make('payroll_id')
                                ->label('Referensi Penggajian')
                                ->relationship('payroll', 'id') // Tips: Idealnya di Model Payroll Anda membuat custom attribute yang menggabungkan Nama & Bulan
                                ->required()
                                ->searchable()
                                ->preload()
                                ->columnSpanFull(),

                            TextInput::make('component_name')
                                ->label('Nama Komponen')
                                ->placeholder('Contoh: Gaji Pokok, BPJS Kesehatan, Uang Makan')
                                ->required()
                                ->maxLength(255),

                            Select::make('type')
                                ->label('Tipe Komponen')
                                ->options([
                                    'allowance' => 'Pendapatan / Tunjangan (+)',
                                    'overtime' => 'Uang Lembur (+)',
                                    'deduction' => 'Potongan / Pajak (-)',
                                ])
                                ->required()
                                ->native(false),

                            TextInput::make('amount')
                                ->label('Nominal Uang')
                                ->required()
                                ->numeric()
                                ->prefix('Rp')
                                ->step(1000)
                                ->columnSpanFull(),
                        ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Komponen')
                    ->schema([
                        TextEntry::make('payroll_id')
                            ->label('ID Penggajian')
                            ->weight(FontWeight::Bold),

                        TextEntry::make('component_name')
                            ->label('Nama Komponen'),

                        TextEntry::make('type')
                            ->label('Sifat Komponen')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'allowance', 'overtime' => 'success', // Hijau (Menambah)
                                'deduction' => 'danger',              // Merah (Mengurangi)
                                default => 'gray',
                            })
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'allowance' => 'Tunjangan',
                                'overtime' => 'Lembur',
                                'deduction' => 'Potongan',
                                default => ucfirst($state),
                            }),

                        TextEntry::make('amount')
                            ->label('Nominal Uang')
                            ->money('IDR', locale: 'id')
                            ->weight(FontWeight::Bold),

                        TextEntry::make('created_at')
                            ->label('Dicatat Pada')
                            ->dateTime('d M Y, H:i')
                            ->color('gray'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(function (Builder $query) {
                if (Auth::user()->hasRole('Karyawan')) {
                    // Cek tembus relasi: PayrollDetail -> Payroll -> Employee
                    $query->whereHas('payroll.employee', function (Builder $query) {
                        $query->where('user_id', Auth::id());
                    });
                }
            })
            ->columns([
                TextColumn::make('payroll.employee.full_name')
                    ->label('Nama Karyawan')
                    ->sortable()
                    ->color('gray'),

                TextColumn::make('component_name')
                    ->label('Nama Komponen')
                    ->searchable()
                    ->weight(FontWeight::SemiBold),

                // UX HACK: Badge Warna untuk Tipe Komponen
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'allowance' => 'success', // Hijau (Positif)
                        'overtime' => 'info',    // Biru (Positif)
                        'deduction' => 'danger',  // Merah (Negatif)
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'allowance' => 'Tunjangan',
                        'overtime' => 'Lembur',
                        'deduction' => 'Potongan',
                        default => ucfirst($state),
                    })
                    ->searchable(),

                TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR', locale: 'id')
                    ->alignEnd() // UX: Rata kanan agar sejajar
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Filter Tipe')
                    ->options([
                        'allowance' => 'Tunjangan',
                        'overtime' => 'Lembur',
                        'deduction' => 'Potongan',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
                    ->icon(Heroicon::OutlinedEllipsisVertical),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Tidak ada rincian komponen')
            ->emptyStateIcon(Heroicon::OutlinedQueueList);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePayrollDetails::route('/'),
        ];
    }
}
