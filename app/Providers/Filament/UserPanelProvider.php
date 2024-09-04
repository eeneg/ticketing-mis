<?php

namespace App\Providers\Filament;

use App\Filament\Auth\LoginPage;
use App\Filament\Auth\RegistrationPage;
use App\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class UserPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->registration(RegistrationPage::class)
            ->databaseNotifications()
            ->profile()
            ->id('user')
            ->path('')
            ->spa()
            ->login(LoginPage::class)
            ->colors([
                'primary' => Color::Blue,
            ])

            ->discoverResources(in: app_path('Filament/User/Resources'), for: 'App\\Filament\\User\\Resources')
            ->discoverPages(in: app_path('Filament/User/Pages'), for: 'App\\Filament\\User\\Pages')
            ->pages([
            ])
            ->discoverWidgets(in: app_path('Filament/User/Widgets'), for: 'App\\Filament\\User\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
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
            ->databaseNotifications()
            ->authMiddleware([
                Authenticate::class,
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label(function () {
                        $role = Auth::user()->role->value;
                        switch ($role) {
                            case 'admin':
                                return 'Admin';
                            case 'support':
                                return 'Support';
                            case 'officer':
                                return 'Officer';
                            default:
                                return;
                        }
                    })
                    ->icon(function () {
                        $role = Auth::user()->role->value;
                        if ($role != 'user') {
                            return 'heroicon-o-user';
                        }
                    })
                    ->url(function () {
                        $role = Auth::user()->role->value;
                        switch ($role) {
                            case 'admin':
                                return route('filament.admin.resources.requests.index');
                            case 'support':
                                return route('filament.support.resources.requests.index');
                            case 'officer':
                                return route('filament.officer.resources.requests.index');
                            default:
                                return;
                        }
                    }),

            ]);

    }
}
