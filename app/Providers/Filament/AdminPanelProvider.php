<?php

namespace App\Providers\Filament;

use App\Http\Middleware\UseAdminSessionCookie;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use App\Filament\Widgets\GameActivityOverview;
use App\Filament\Widgets\GameActivityDetails;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->resources($this->enabledPluginResources())
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                GameActivityOverview::class,
                GameActivityDetails::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make()
                    ->registerNavigation(
                        fn(): bool => auth()->user()?->hasRole('super_admin') ?? false
                    ),
            ])
            ->middleware([
                // Must run before cookie encryption and session startup so the
                // panel initializes its session with the dedicated admin cookie.
                UseAdminSessionCookie::class,
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

    private function enabledPluginResources(): array
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('installed_plugins')) return [];
            $classes = [];
            foreach (\App\Models\InstalledPlugin::query()->where('is_enabled', true)->get(['manifest']) as $plugin) {
                $manifest = is_array($plugin->manifest) ? $plugin->manifest : [];
                foreach (($manifest['filament_resources'] ?? []) as $class) {
                    if (is_string($class) && class_exists($class)
                        && is_subclass_of($class, \Filament\Resources\Resource::class)) {
                        $classes[] = $class;
                    }
                }
            }
            return array_values(array_unique($classes));
        } catch (\Throwable $exception) {
            report($exception);
            return [];
        }
    }
}
