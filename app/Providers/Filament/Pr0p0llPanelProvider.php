<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Pages\Login;
use App\Filament\Pages\UpdateUserData;
use App\Filament\Widgets\NeedsDataReviewWidget;
use Filament\Enums\ThemeMode;
use Filament\FilamentManager;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use Filament\Facades\Filament;
use Illuminate\Contracts\View\View;

class Pr0p0llPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        Filament::registerRenderHook('panels::global-search.after',
            fn (): View => view('filament.header.aftersearch'),
        );

        return $panel
            ->default()
            ->id('pr0p0ll')
            ->path('pr0p0ll')
            ->login()
            ->colors([
                'primary' => '#ee4d2e',
            ])
            ->viteTheme('resources/css/filament/pr0p0ll/theme.css')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->login(Login::class)
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                NeedsDataReviewWidget::class,
            ])
            ->plugins([FilamentApexChartsPlugin::make()])
            ->profile(UpdateUserData::class)
            ->userMenuItems([
                MenuItem::make()->label('Startseite')->url('/')->icon('heroicon-o-home'),
            ])
            ->font('Inter')
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
            ->brandLogo(fn () => view('filament.admin.logo'))
            ->brandLogoHeight('auto')
            ->defaultThemeMode(ThemeMode::Dark)
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
