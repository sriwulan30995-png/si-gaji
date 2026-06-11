<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label(__('role.delete_role'))
                ->icon(Heroicon::Trash)
                ->hidden(function ($record) {
                    // Ambil semua role ids yang dimiliki user saat ini
                    $userRoleIds = Auth::user()->roles->pluck('id')->toArray();
                    // Sembunyikan tombol jika role ini termasuk milik user
                    return in_array($record->id, $userRoleIds);
                }),
        ];
    }
}
