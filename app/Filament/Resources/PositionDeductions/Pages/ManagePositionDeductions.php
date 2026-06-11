<?php

namespace App\Filament\Resources\PositionDeductions\Pages;

use App\Filament\Resources\PositionDeductions\PositionDeductionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePositionDeductions extends ManageRecords
{
    protected static string $resource = PositionDeductionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
