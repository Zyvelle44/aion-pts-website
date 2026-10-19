<?php

namespace App\Http\Middleware;

use App\Models\InstalledPlugin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstalledPluginEnabled
{
    public function handle(Request $request, Closure $next, string $pluginId): Response
    {
        $plugin = InstalledPlugin::query()->where('plugin_id', $pluginId)->first();
        abort_unless($plugin && $plugin->is_enabled, 404);

        return $next($request);
    }
}
