<?php

namespace App\Filament\Resources\Roles;

use App\Filament\Clusters\RolePermission\RolePermissionCluster;
use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Filament\Resources\Roles\Pages\ViewRole;
use App\Filament\Resources\Roles\Schemas\RoleForm;
use App\Filament\Resources\Roles\Schemas\RoleInfolist;
use App\Filament\Resources\Roles\Tables\RolesTable;
use App\Models\Role;
use BackedEnum;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;
    protected static ?string $cluster = RolePermissionCluster::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;
    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Identification;
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Start;
    public static function getNavigationLabel(): string
    {
        return __('role.role_label');
    }
    public static function getModelLabel(): string
    {
        return __('role.role_label');
    }
    public static function getPluralModelLabel(): string
    {
        return __('role.roles');
    }
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?int $navigationSort = 18;
    protected static ?string $slug = 'roles';


    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RoleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table);
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
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'view' => ViewRole::route('/{record}'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }
}
