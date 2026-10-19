<?php

namespace App\Http\Middleware;

use App\Models\ModulePlugin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeatureEnabled
{
    public function handle(Request $request, Closure $next, string $featureKey): Response
    {
        abort_unless(ModulePlugin::featureEnabled($featureKey), 404);

        return $next($request);
    }
}
