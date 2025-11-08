<?php

namespace App\Providers\Filament;

use App\Filament\Pages\App\Profile;
use App\Filament\Pages\Auth\Login;
use App\Filament\Widgets;
use Bytexr\QueueableBulkActions\Enums\StatusEnum;
use Bytexr\QueueableBulkActions\QueueableBulkActionsPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\View\View;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('/')
            ->login(Login::class)
            ->passwordReset()
            ->sidebarCollapsibleOnDesktop()
            ->sidebarFullyCollapsibleOnDesktop()
            ->databaseNotifications()
            ->profile(Profile::class, false)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
                FilamentFullCalendarPlugin::make()
                    ->selectable()
                    ->editable(),
                QueueableBulkActionsPlugin::make()
                    ->pollingInterval('5s')
                    ->resource(\App\Filament\Resources\BulkActionResource::class)
                    ->queue('database', 'default')
                    ->colors([
                        StatusEnum::QUEUED->value => 'slate',
                        StatusEnum::IN_PROGRESS->value => 'info',
                        StatusEnum::FINISHED->value => 'success',
                        StatusEnum::FAILED->value => 'danger',
                    ]),
                \DiogoGPinto\AuthUIEnhancer\AuthUIEnhancerPlugin::make()
                    ->showEmptyPanelOnMobile(true)
                    ->mobileFormPanelPosition('bottom')
                    ->emptyPanelBackgroundImageUrl('images/office-dark.jpeg'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->widgets([
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
            ])->authMiddleware([
                Authenticate::class,
            ])
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
            ]);
    }
}
