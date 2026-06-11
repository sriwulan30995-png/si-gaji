<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Howdu\FilamentRecordSwitcher\Filament\Concerns\HasRecordSwitcher;
use Illuminate\Support\Facades\Auth;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label(__('user.delete_user'))
                ->icon(Heroicon::Trash)
                ->visible(fn(): bool => $this->record->id !== Auth::id()),
        ];
    }
}
