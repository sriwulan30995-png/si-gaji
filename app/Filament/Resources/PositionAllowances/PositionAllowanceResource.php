<?php

namespace App\Filament\Resources\PositionAllowances;

use App\Filament\Resources\PositionAllowances\Pages\ManagePositionAllowances;
use App\Models\PositionAllowance;
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

class PositionAllowanceResource extends Resource
{
    protected static ?string $model = PositionAllowance::class;
    // --- Konfigurasi Navigasi (UI/UX) ---
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;
    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Squares2x2;

    protected static ?string $navigationLabel = 'Tunjangan Jabatan';
    protected static ?string $modelLabel = 'Tunjangan Jabatan';
    protected static ?string $pluralModelLabel = 'Mapping Tunjangan Jabatan';

    // Dikelompokkan bersama pengaturan komponen gaji lainnya
    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan Gaji';
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pemetaan Tunjangan')
                    ->description('Tentukan nominal tunjangan yang akan diikat pada jabatan tertentu.')
                    ->icon(Heroicon::OutlinedLink)
                    ->schema([
                        Grid::make(2)->schema([
                            // UX HACK: Dropdown pencarian Jabatan
                            Select::make('position_id')
                                ->label('Jabatan')
                                ->relationship('position', 'position_name') // Pastikan relasi 'position' ada di model PositionAllowance
                                ->required()
                                ->searchable()
                                ->preload(),

                            // UX HACK: Dropdown pencarian Jenis Tunjangan
                            Select::make('allowance_type_id')
                                ->label('Jenis Tunjangan')
                                ->relationship('allowanceType', 'name') // Pastikan relasi 'allowanceType' ada di model
                                ->required()
                                ->searchable()
                                ->preload(),

                            // Nominal Uang (Full Width agar lega)
                            TextInput::make('amount')
                                ->label('Nominal Tunjangan')
                                ->required()
                                ->numeric()
                                ->prefix('Rp')
                                ->columnSpanFull()
                                ->helperText('Masukkan nominal tetap, atau nominal harian jika tunjangan ini bersifat harian.'),
                        ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Tunjangan Jabatan')
                    ->schema([
                        TextEntry::make('position.position_name')
                            ->label('Jabatan')
                            ->weight(FontWeight::Bold)
                            ->color('primary'),

                        TextEntry::make('allowanceType.name')
                            ->label('Jenis Tunjangan')
                            ->weight(FontWeight::SemiBold),

                        TextEntry::make('amount')
                            ->label('Nominal')
                            ->money('IDR', locale: 'id')
                            ->weight(FontWeight::Bold)
                            ->color('success'),

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

                TextColumn::make('allowanceType.name')
                    ->label('Jenis Tunjangan')
                    ->searchable()
                    ->sortable()
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->weight(FontWeight::SemiBold),

                TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR', locale: 'id')
                    ->alignEnd() // Rata Kanan untuk Uang
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Didaftarkan Pada')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // UX HACK: Filter sangat krusial agar HRD bisa memantau per jabatan
                SelectFilter::make('position_id')
                    ->label('Filter Jabatan')
                    ->relationship('position', 'position_name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('allowance_type_id')
                    ->label('Filter Jenis Tunjangan')
                    ->relationship('allowanceType', 'name'),
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
            ->emptyStateHeading('Belum ada pemetaan tunjangan')
            ->emptyStateDescription('Silakan kaitkan jabatan dengan jenis tunjangan yang tersedia.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePositionAllowances::route('/'),
        ];
    }
}