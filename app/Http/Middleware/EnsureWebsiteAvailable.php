<?php

namespace App\Http\Middleware;

use App\Models\WebsiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWebsiteAvailable
{
    public function handle(Request $request, Closure $next): Response
    {
        $enabled = filter_var(WebsiteSetting::get('maintenance_mode', false), FILTER_VALIDATE_BOOLEAN);

        if ($enabled) {
            return response()->view('errors.maintenance', [
                'message' => WebsiteSetting::get('maintenance_message', 'We will be back soon.'),
                'siteName' => WebsiteSetting::get('site_name', 'AION PTS'),
            ], 503)->header('Retry-After', '300');
        }

        return $next($request);
    }
}
