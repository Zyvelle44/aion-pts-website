<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UseAdminSessionCookie
{
    public function handle(Request $request, Closure $next): Response
    {
        $originalCookieName = config('session.cookie');

        if (! $this->isAdminRequest($request)) {
            return $next($request);
        }

        $adminCookieName = (string) config(
            'session.admin_cookie',
            (string) config('app.name', 'laravel') . '-admin-session'
        );
        $adminCookieName = preg_replace('/[^A-Za-z0-9_-]/', '-', $adminCookieName);

        if (! is_string($adminCookieName) || $adminCookieName === '') {
            $adminCookieName = 'laravel-admin-session';
        }

        config(['session.cookie' => $adminCookieName]);

        try {
            return $next($request);
        } finally {
            config(['session.cookie' => $originalCookieName]);
        }
    }

    private function isAdminRequest(Request $request): bool
    {
        if ($request->is('admin') || $request->is('admin/*')) {
            return true;
        }

        $referer = $request->headers->get('referer');
        if (! is_string($referer) || $referer === '') {
            return false;
        }

        $refererPath = parse_url($referer, PHP_URL_PATH);

        return is_string($refererPath)
            && ($refererPath === '/admin' || str_starts_with($refererPath, '/admin/'));
    }
}
