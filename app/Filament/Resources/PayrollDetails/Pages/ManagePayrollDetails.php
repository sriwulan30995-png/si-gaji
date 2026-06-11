<?php

namespace App\Filament\Resources\PayrollDetails\Pages;

use App\Filament\Resources\PayrollDetails\PayrollDetailResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePayrollDetails extends ManageRecords
{
    protected static string $resource = PayrollDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
