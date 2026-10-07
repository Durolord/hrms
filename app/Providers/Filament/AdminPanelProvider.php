<?php

namespace App\Providers\Filament;

use App\Filament\Pages\App\Profile;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Dashboard;
use App\Filament\Resources\BulkActionResource;
use App\Filament\Widgets;
use App\Support\Demo;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Bytexr\QueueableBulkActions\Enums\StatusEnum;
use Bytexr\QueueableBulkActions\QueueableBulkActionsPlugin;
use DiogoGPinto\AuthUIEnhancer\AuthUIEnhancerPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\View\View;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        // Shared demo accounts have no inbox, so there is nothing to reset in demo mode.
        if (! Demo::enabled()) {
            $panel->passwordReset();
        }

        return $panel
            ->default()
            ->id('admin')
            ->path('/')
            ->login(Login::class)
            ->sidebarCollapsibleOnDesktop()
            ->sidebarFullyCollapsibleOnDesktop()
            ->databaseNotifications()
            ->profile(Profile::class, false)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->brandName('HRMS')
            ->brandLogo(fn (): View => view('components.brand-logo'))
            ->brandLogoHeight('4.5rem')
            ->font('Inter')
            ->maxContentWidth(MaxWidth::Full)
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->globalSearchDebounce('300ms')
            ->unsavedChangesAlerts()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
                FilamentFullCalendarPlugin::make()
                    ->selectable()
                    ->editable(),
                QueueableBulkActionsPlugin::make()
                    ->pollingInterval('5s')
                    ->resource(BulkActionResource::class)
                    ->queue('database', 'default')
                    ->colors([
                        StatusEnum::QUEUED->value => 'slate',
                        StatusEnum::IN_PROGRESS->value => 'info',
                        StatusEnum::FINISHED->value => 'success',
                        StatusEnum::FAILED->value => 'danger',
                    ]),
                AuthUIEnhancerPlugin::make()
                    ->showEmptyPanelOnMobile(true)
                    ->mobileFormPanelPosition('bottom')
                    ->emptyPanelBackgroundImageUrl('images/office-dark.jpeg'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                Widgets\PendingApprovalsWidget::class,
                Widgets\MyLeaveBalanceWidget::class,
                Widgets\WhosOutWidget::class,
                Widgets\AnniversariesWidget::class,
                Widgets\MyAttendance::class,
                Widgets\MyLeaves::class,
                Widgets\MyPayrolls::class,
                Widgets\AttendanceSummaryWidget::class,
                Widgets\OrganizationOverview::class,
                Widgets\PayrollSummaryChartWidget::class,
                Widgets\EmployeeDistributionWidget::class,
            ])
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
            // A tighter request budget per IP for the public demo (Livewire requests included).
            ->middleware(Demo::enabled() ? [ThrottleRequests::using('demo')] : [], isPersistent: true)
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_START,
                fn (): View => view('components.favicon'),
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => (Demo::enabled() ? '<meta name="robots" content="noindex, nofollow">' : '')
                    .'<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&display=swap" rel="stylesheet">',
            )
            ->renderHook(
                PanelsRenderHook::BODY_START,
                fn (): ?View => Demo::enabled() ? view('components.demo-banner') : null,
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn (): ?View => Demo::enabled() ? view('filament.pages.auth.demo-accounts') : null,
                scopes: Login::class,
            )
            ->renderHook(
                'panels::footer',
                fn (): View => view('components.loading-footer'),
            )
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Admin')
                    ->icon('heroicon-o-wrench-screwdriver'),
                NavigationGroup::make()
                    ->label('Personal')
                    ->icon('heroicon-o-user'),
                NavigationGroup::make()
                    ->label('User Management')
                    ->icon('heroicon-o-user-circle'),
                NavigationGroup::make()
                    ->label('Employee Management')
                    ->icon('heroicon-o-user-group'),
                NavigationGroup::make()
                    ->label('Organizational Structure')
                    ->icon('heroicon-o-building-office-2'),
                NavigationGroup::make()
                    ->label('Recruitment and Openings')
                    ->icon('heroicon-o-briefcase'),
                NavigationGroup::make()
                    ->label('Project Management')
                    ->icon('heroicon-o-clipboard-document-list'),
                NavigationGroup::make()
                    ->label('Payroll and Compensation')
                    ->icon('heroicon-o-banknotes'),
                NavigationGroup::make()
                    ->label('Notes and Records Management')
                    ->icon('heroicon-o-document-text'),
                NavigationGroup::make()
                    ->label('Help & Documentation')
                    ->icon('heroicon-o-lifebuoy')
                    ->collapsed(),
            ]);
    }
}
