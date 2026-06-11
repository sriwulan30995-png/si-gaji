<?php

namespace App\Filament\Resources\PositionDeductions;

use App\Filament\Resources\PositionDeductions\Pages\ManagePositionDeductions;
use App\Models\PositionDeduction;
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
use UnitEnum;

class PositionDeductionResource extends Resource
{
    protected static ?string $model = PositionDeduction::class;

    // --- Konfigurasi Navigasi (UI/UX) ---
    // Menggunakan ikon Dokumen Minus untuk melambangkan potongan
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentMinus;
    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::DocumentMinus;

    protected static ?string $navigationLabel = 'Potongan Jabatan';
    protected static ?string $modelLabel = 'Potongan Jabatan';
    protected static ?string $pluralModelLabel = 'Mapping Potongan Jabatan';

    // Dikelompokkan bersama pengaturan komponen gaji lainnya
    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan Gaji';
    protected static ?int $navigationSort = 6; // Diletakkan tepat di bawah Tunjangan Jabatan

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pemetaan Potongan')
                    ->description('Tentukan besaran potongan yang dibebankan pada jabatan tertentu (misal: BPJS, PPh21).')
                    ->icon(Heroicon::OutlinedReceiptRefund)
                    ->schema([
                        Grid::make(2)->schema([
                            // UX HACK: Dropdown pencarian Jabatan
                            Select::make('position_id')
                                ->label('Jabatan')
                                ->relationship('position', 'position_name')
                                ->required()
                                ->searchable()
                                ->preload(),

                            // UX HACK: Dropdown pencarian Jenis Potongan
                            Select::make('deduction_type_id')
                                ->label('Jenis Potongan')
                                ->relationship('deductionType', 'name')
                                ->required()
                                ->searchable()
                                ->preload(),

                            // Nominal Uang / Persentase
                            TextInput::make('amount')
                                ->label('Besaran Potongan (Nominal / Persentase)')
                                ->required()
                                ->numeric()
                                ->step(0.01) // Memungkinkan input desimal jika itu persentase (misal: 2.5)
                                ->columnSpanFull()
                                ->helperText('Masukkan nominal uang (contoh: 50000) atau nilai persentase (contoh: 2.5) tergantung pada sifat Jenis Potongan yang Anda pilih.'),
                        ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Potongan Jabatan')
                    ->schema([
                        TextEntry::make('position.position_name')
                            ->label('Jabatan')
                            ->weight(FontWeight::Bold)
                            ->color('primary'),

                        TextEntry::make('deductionType.name')
                            ->label('Jenis Potongan')
                            ->weight(FontWeight::SemiBold),

                        // UX HACK: Diberi warna merah (danger) karena ini adalah pengurang
                        TextEntry::make('amount')
                            ->label('Besaran (Nilai)')
                            ->weight(FontWeight::Bold)
                            ->color('danger'),

                        TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime('d M Y, H:i')
                            ->color('gray'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('position.position_name')
                    ->label('Jabatan')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('deductionType.name')
                    ->label('Jenis Potongan')
                    ->searchable()
                    ->sortable()
                    ->icon(Heroicon::OutlinedReceiptRefund)
                    ->weight(FontWeight::SemiBold),

                // UX HACK: Warna merah (danger) untuk mempertegas "Potongan"
                TextColumn::make('amount')
                    ->label('Besaran Nilai')
                    ->alignEnd()
                    ->sortable()
                    ->color('danger')
                    ->description(fn($record) => 'Sesuai tipe potongan'), // Opsional: Memberi konteks bahwa ini bisa Rp atau %

                TextColumn::make('created_at')
                    ->label('Didaftarkan Pada')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('position_id')
                    ->label('Filter Jabatan')
                    ->relationship('position', 'position_name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('deduction_type_id')
                    ->label('Filter Jenis Potongan')
                    ->relationship('deductionType', 'name'),
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
            ->emptyStateHeading('Belum ada pemetaan potongan')
            ->emptyStateDescription('Silakan kaitkan jabatan dengan jenis potongan seperti BPJS atau Pajak.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePositionDeductions::route('/'),
        ];
    }
}
