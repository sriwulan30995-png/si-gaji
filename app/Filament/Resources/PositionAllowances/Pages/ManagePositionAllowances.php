<?php

namespace App\Filament\Resources\PositionAllowances\Pages;

use App\Filament\Resources\PositionAllowances\PositionAllowanceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePositionAllowances extends ManageRecords
{
    protected static string $resource = PositionAllowanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
