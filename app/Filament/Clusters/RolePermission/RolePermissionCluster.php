<?php

namespace App\Filament\Clusters\RolePermission;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class RolePermissionCluster extends Cluster
{
    protected static ?string $navigationLabel = 'Akses dan Perizinan';
    protected static ?string $title = 'Akses dan Perizinan';
    protected static ?int $navigationSort = 20;
    protected static string|UnitEnum|null $navigationGroup = 'Kelola Pengguna';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;
    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::ShieldCheck;
}
