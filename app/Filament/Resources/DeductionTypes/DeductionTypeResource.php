<?php

namespace App\Filament\Resources\DeductionTypes;

use App\Filament\Resources\DeductionTypes\Pages\ManageDeductionTypes;
use App\Models\DeductionType;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class DeductionTypeResource extends Resource
{
    protected static ?string $model = DeductionType::class;

    // --- Konfigurasi Navigasi (UI/UX) ---
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptRefund;
    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::ReceiptRefund;

    protected static ?string $navigationLabel = 'Jenis Potongan';
    protected static ?string $modelLabel = 'Jenis Potongan';
    protected static ?string $pluralModelLabel = 'Kategori Potongan';

    // Langsung menggunakan string sesuai pola sebelumnya
    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan Gaji';

    protected static ?int $navigationSort = 4; // Berada di bawah Jenis Tunjangan (3)
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $slug = 'jenis-potongan';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Definisi Potongan')
                    ->description('Buat kategori potongan baru yang nantinya akan dibebankan ke jabatan tertentu.')
                    ->icon(Heroicon::OutlinedDocumentMinus) // Ikon dokumen dengan tanda minus
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Potongan')
                            ->placeholder('Contoh: BPJS Kesehatan, PPh 21, Pinjaman Karyawan')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Toggle::make('is_percentage')
                            ->label('Dihitung dalam Persentase (%)?')
                            ->helperText('Aktifkan jika potongan ini berupa % dari gaji pokok (contoh: BPJS 1%). Biarkan mati jika potongan berupa nominal uang tetap (contoh: Pinjaman Rp 200.000).')
                            ->default(false),
                    ])->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Jenis Potongan')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nama Potongan')
                            ->weight(FontWeight::Bold),

                        IconEntry::make('is_percentage')
                            ->label('Metode Pemotongan')
                            ->boolean()
                            ->trueIcon(Heroicon::ReceiptPercent) // UX: Ikon Persen jika true
                            ->falseIcon(Heroicon::Banknotes),    // UX: Ikon Uang jika false

                        TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime('d M Y')
                            ->color('gray'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Potongan')
                    ->searchable()
                    ->weight(FontWeight::SemiBold)
                    ->icon(Heroicon::OutlinedReceiptRefund),

                // UX HACK: Mengubah tampilan Boolean menjadi Teks Badge
                TextColumn::make('is_percentage')
                    ->label('Tipe Potongan')
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Persentase dari Gaji (%)' : 'Nominal Tetap (Rp)')
                    ->badge()
                    // Menggunakan warna warning (kuning/oranye) untuk persentase, dan danger (merah) untuk nominal, 
                    // sebagai indikator visual "potongan/pengurangan".
                    ->color(fn(bool $state): string => $state ? 'warning' : 'danger'),

                TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
                    ->icon(Heroicon::OutlinedEllipsisVertical)
                    ->tooltip('Opsi'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada jenis potongan')
            ->emptyStateDescription('Tambahkan kategori potongan baru seperti BPJS, Pajak, atau Pinjaman.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageDeductionTypes::route('/'),
        ];
    }
}
