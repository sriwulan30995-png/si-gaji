<?php

namespace App\Providers\Filament;

use Andreia\FilamentNordTheme\FilamentNordThemePlugin;
use App\Filament\Pages\EditProfile;
use App\Filament\Pages\Login;
use Caresome\FilamentAuthDesigner\AuthDesignerPlugin;
use Caresome\FilamentAuthDesigner\Enums\MediaPosition;
use Caresome\FilamentNeobrutalism\NeobrutalismeTheme;
use Devonab\FilamentEasyFooter\EasyFooterPlugin;
use Filafly\Themes\Brisk\BriskTheme;
use Filament\Enums\ThemeMode;
use Filament\Enums\UserMenuPosition;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Resma\FilamentAwinTheme\FilamentAwinTheme;
use WatheqAlshowaiter\FilamentStickyTableHeader\StickyTableHeaderPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $themePlugin = $this->themePlugin();

        return $panel
            ->default()
            ->spa()
            ->id('admin')
            ->path('/')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login(Login::class)
            ->profile(EditProfile::class)
            // ->brandLogo('/images/logo/laravelchezzy.png')
            ->brandLogoHeight('2.5rem')
            ->defaultThemeMode(ThemeMode::System)
            ->colors([
                'danger' => Color::Rose,
                'gray' => Color::Gray,
                'info' => Color::Blue,
                'primary' => Color::Indigo,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])
            ->databaseNotifications()
            ->userMenu(position: UserMenuPosition::Topbar)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                // AccountWidget::class,
                // FilamentInfoWidget::class,
            ])
            ->when(
                $themePlugin,
                fn(Panel $panel) => $panel->plugin($themePlugin)
            )
            // FOOTER PLUGIN
            ->plugins([
                EasyFooterPlugin::make()
                    ->withBorder()
                    ->withSentence('LaravelChezzy. All rights reserved.'),
            ])
            // AUTH PLUGINS
            ->plugin(
                AuthDesignerPlugin::make()
                    ->defaults(
                        fn($config) => $config
                            ->media(asset('images/auth/background-auth.png'))
                            ->mediaPosition(MediaPosition::Left)
                            ->blur(0)
                            ->mediasize('70%')
                    )
                    ->themeToggle()
            )
            // STICKY TABLE HEADER PLUGIN
            // ->plugin(
            //     StickyTableHeaderPlugin::make(),
            // )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    private function themePlugin(): ?object
    {
        return match (config('chezzy.active_theme')) {
            'neobrutalism' => NeobrutalismeTheme::make()
                ->customize([
                    'border-width' => '2px',
                    'shadow-offset-md' => '3px',
                    'radius-md' => '0.5rem',
                ]),

            'brisk' => BriskTheme::make(),

            'nord' => FilamentNordThemePlugin::make(),

            'awin' => FilamentAwinTheme::make(),

            default => null,
        };
    }
}
