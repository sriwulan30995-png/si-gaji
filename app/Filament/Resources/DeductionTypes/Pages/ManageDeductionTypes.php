<?php

namespace App\Filament\Resources\DeductionTypes\Pages;

use App\Filament\Resources\DeductionTypes\DeductionTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageDeductionTypes extends ManageRecords
{
    protected static string $resource = DeductionTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
