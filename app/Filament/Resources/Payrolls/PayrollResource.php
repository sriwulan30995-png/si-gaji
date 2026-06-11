<?php

namespace App\Filament\Resources\Payrolls;

use App\Filament\Resources\Payrolls\Pages\CreatePayroll;
use App\Filament\Resources\Payrolls\Pages\EditPayroll;
use App\Filament\Resources\Payrolls\Pages\ListPayrolls;
use App\Filament\Resources\Payrolls\Pages\ViewPayroll;
use App\Filament\Resources\Payrolls\Schemas\PayrollForm;
use App\Filament\Resources\Payrolls\Schemas\PayrollInfolist;
use App\Filament\Resources\Payrolls\Tables\PayrollsTable;
use App\Models\Payroll;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;
    // --- Konfigurasi Navigasi (UI/UX) ---
    // Menggunakan ikon kartu kredit/pembayaran untuk merepresentasikan "Gaji"
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;
    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::CreditCard;

    protected static ?string $navigationLabel = 'Penggajian';
    protected static ?string $modelLabel = 'Slip Gaji';
    protected static ?string $pluralModelLabel = 'Riwayat Penggajian';

    // Grup Khusus Eksekusi Akhir Bulan
    protected static string|UnitEnum|null $navigationGroup = 'Eksekusi';

    protected static ?int $navigationSort = 7;
    protected static ?string $slug = 'penggajian';

    public static function form(Schema $schema): Schema
    {
        return PayrollForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PayrollInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PayrollsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayrolls::route('/'),
            'create' => CreatePayroll::route('/create'),
            'view' => ViewPayroll::route('/{record}'),
            'edit' => EditPayroll::route('/{record}/edit'),
        ];
    }
}
