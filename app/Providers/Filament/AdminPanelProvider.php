<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\AccreditationProgress;
use App\Filament\Widgets\QualityOverview;
use App\Filament\Widgets\PpeppQualityCharts;
use App\Filament\Widgets\VerifiedSpmiProgramsChart;
use App\Http\Middleware\ResolveTenantContext;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Support\Assets\Css;
use Illuminate\Support\Facades\Blade; // <-- Tambahkan Import Blade ini

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->databaseNotifications()
            ->databaseNotificationsPolling('60s')
            ->sidebarCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->navigationGroups([
                NavigationGroup::make('Organisasi & Tenant')->icon(Heroicon::OutlinedBuildingOffice2),
                NavigationGroup::make('Security')->icon(Heroicon::OutlinedShieldCheck),
                NavigationGroup::make('SPMI')->icon(Heroicon::OutlinedChartBarSquare),
                NavigationGroup::make('AMI & Tindak Lanjut Mutu')->icon(Heroicon::OutlinedClipboardDocumentCheck),
                NavigationGroup::make('Evidence Center')->icon(Heroicon::OutlinedCloud),
                NavigationGroup::make('Instrument Registry')->icon(Heroicon::OutlinedBookOpen),
                NavigationGroup::make('Akreditasi')->icon(Heroicon::OutlinedAcademicCap),
                NavigationGroup::make('Reporting')->icon(Heroicon::OutlinedChartBarSquare),
            ])
            // Render Hook 1: Impersonation Banner
            ->renderHook(
                PanelsRenderHook::TOPBAR_START,
                fn(): string => view('filament.components.impersonation-banner')->render()
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                // AccountWidget::class,
                // FilamentInfoWidget::class,
                QualityOverview::class,
                AccreditationProgress::class,
                PpeppQualityCharts::class,
                VerifiedSpmiProgramsChart::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                ResolveTenantContext::class,
            ]);
    }
}
