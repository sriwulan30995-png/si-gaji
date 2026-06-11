<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Halaman Utama';
    protected static string $routePath = '/';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;
    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Home;
    protected static ?int $navigationSort = -2;
}
