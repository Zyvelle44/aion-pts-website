<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use App\Models\WebsiteSetting;
use App\Models\ModulePlugin;
use App\Models\InstalledPlugin;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use App\Policies\RolePolicy;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Build the plugin namespace map once per PHP process. The previous
        // autoloader rescanned every plugin directory and decoded every manifest
        // for each unresolved class, adding avoidable filesystem work to requests.
        spl_autoload_register(static function (string $class): void {
            static $namespaceMap = null;

            if ($namespaceMap === null) {
                $namespaceMap = [];
                $pluginRoot = storage_path('app/aion-plugins');

                if (is_dir($pluginRoot)) {
                    foreach (\Illuminate\Support\Facades\File::directories($pluginRoot) as $directory) {
                        $manifestPath = $directory.'/plugin.json';
                        if (! is_file($manifestPath)) continue;

                        $manifest = json_decode((string) file_get_contents($manifestPath), true);
                        $namespace = is_array($manifest) ? ($manifest['namespace'] ?? null) : null;

                        if (is_string($namespace) && $namespace !== '') {
                            $namespaceMap[$namespace] = $directory.'/src';
                        }
                    }

                    uksort($namespaceMap, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));
                }
            }

            foreach ($namespaceMap as $namespace => $sourceRoot) {
                if (! str_starts_with($class, $namespace.'\\')) continue;

                $relative = substr($class, strlen($namespace) + 1);
                if ($relative === '' || str_contains($relative, '..')) return;

                $file = $sourceRoot.'/'.str_replace('\\', '/', $relative).'.php';
                if (is_file($file)) require_once $file;
                return;
            }
        }, true, false);
    }

    public function boot(): void
    {
        Gate::policy(Role::class, RolePolicy::class);

        // Register view namespaces for installed packages without changing the core layout.
        try {
            if (Schema::hasTable('installed_plugins')) {
                Cache::remember('aion.installed_plugin_view_namespaces.v1', 30, fn () => InstalledPlugin::query()->get(['plugin_id'])->map(fn (InstalledPlugin $plugin) => $plugin->plugin_id)->all());
                foreach (Cache::get('aion.installed_plugin_view_namespaces.v1', []) as $pluginId) {
                    $plugin = new InstalledPlugin(['plugin_id' => $pluginId]);
                    $views = storage_path('app/aion-plugins/'.$plugin->plugin_id.'/resources/views');
                    if (is_dir($views)) {
                        View::addNamespace('aion-plugin-'.$plugin->plugin_id, $views);
                    }
                }
            }
        } catch (\Throwable $exception) {
            // Keep Artisan commands and first-install boot working before migrations are applied.
        }

        // Keep each account action on its own limiter so normal registration,
        // login and password-change traffic do not consume one shared bucket.
        RateLimiter::for('aion-register', function (Request $request) {
            return Limit::perMinute(5)->by('aion-register:'.$request->ip());
        });

        RateLimiter::for('aion-login', function (Request $request) {
            // Apply both an IP-wide ceiling and a username-specific ceiling.
            // This prevents bypassing the login limit by rotating usernames or IPs.
            $username = Str::lower(trim((string) $request->input('username', '')));

            return [
                Limit::perMinute(30)->by('aion-login-ip:'.$request->ip()),
                Limit::perMinute(8)->by('aion-login-user:'.$username),
            ];
        });

        RateLimiter::for('aion-password-change', function (Request $request) {
            $uid = (string) $request->session()->get('aion_account_uid', 'unknown');
            return Limit::perMinutes(10, 5)->by('aion-password-change:'.$request->ip().'|'.$uid);
        });

        // Share editable website settings with Blade views.
        View::composer('*', function ($view) {
            $view->with('websiteSettings', [
                'site_name' => WebsiteSetting::get('site_name', 'AION PTS'),
                'tagline' => WebsiteSetting::get('tagline', 'By Zyvalle'),
                'meta_description' => WebsiteSetting::get('meta_description', 'Aion MMORPG 4.6 Private Test Server.'),
                'logo_path' => WebsiteSetting::get('logo_path', ''),
                'favicon_path' => WebsiteSetting::get('favicon_path', ''),
                'server_name' => WebsiteSetting::get('server_name', 'AION PTS'),
                'server_version' => WebsiteSetting::get('server_version', '4.6'),
                'server_rate' => WebsiteSetting::get('server_rate', ''),
                'level_cap' => WebsiteSetting::get('level_cap', ''),
                'contact_email' => WebsiteSetting::get('contact_email', ''),
                'discord_url' => WebsiteSetting::get('discord_url', ''),
                'facebook_url' => WebsiteSetting::get('facebook_url', ''),
                'maintenance_mode' => filter_var(WebsiteSetting::get('maintenance_mode', false), FILTER_VALIDATE_BOOLEAN),
                'maintenance_message' => WebsiteSetting::get('maintenance_message', 'We will be back soon.'),
            ]);
            $view->with('aionFeatures', collect([
                'home', 'news', 'info', 'download', 'donation', 'support',
                'account.registration', 'account.login', 'account.characters', 'account.password', 'server.status',
            ])->mapWithKeys(fn (string $key): array => [$key => ModulePlugin::featureEnabled($key)])->all());
            $hooks = [];
            $navigation = [];
            $accountNavigation = [];
            try {
                if (Schema::hasTable('installed_plugins')) {
                    foreach (Cache::remember('aion.enabled_plugin_manifests.v1', 30, fn () => InstalledPlugin::query()->where('is_enabled', true)->orderBy('name')->get(['plugin_id', 'manifest'])->map(fn (InstalledPlugin $plugin) => ['plugin_id' => $plugin->plugin_id, 'manifest' => is_array($plugin->manifest) ? $plugin->manifest : []])->all()) as $pluginData) {
                        $manifest = $pluginData['manifest'];
                        if (filled($manifest['hooks']['topbar'] ?? null)) {
                            // A later enabled plugin may override the topbar; installation order is stable by name.
                            $hooks['topbar'] = $manifest['hooks']['topbar'];
                        }
                        if (filled($manifest['hooks']['footer'] ?? null)) {
                            $hooks['footer'] = $manifest['hooks']['footer'];
                        }
                        foreach (($manifest['navigation'] ?? []) as $item) {
                            if (! is_array($item) || ! filled($item['label'] ?? null)) continue;
                            if (($item['requires_login'] ?? false) && ! session()->has('aion_account_username')) continue;
                            if (! ($item['requires_login'] ?? false) && ($item['guest_only'] ?? false) && session()->has('aion_account_username')) continue;
                            $entry = [
                                'label' => (string) $item['label'],
                                'url' => (string) ($item['url'] ?? '#'),
                            ];
                            if (($item['placement'] ?? 'global') === 'account_sidebar') {
                                if (session()->has('aion_account_username')) {
                                    $accountNavigation[] = $entry;
                                }
                            } else {
                                $navigation[] = $entry;
                            }
                        }
                    }
                }
            } catch (\Throwable $exception) {
                // Plugin integration must not break core page rendering if the registry is unavailable.
            }
            $view->with('aionPluginHooks', $hooks);
            $view->with('aionPluginNavigation', $navigation);
            $view->with('aionAccountPluginNavigation', $accountNavigation);
        });
    }
}
