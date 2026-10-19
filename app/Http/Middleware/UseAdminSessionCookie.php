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
        $adminCookieName = (string) config(
            'session.admin_cookie',
            (string) config('app.name', 'laravel') . '-admin-session'
        );
        $adminCookieName = preg_replace('/[^A-Za-z0-9_-]/', '-', $adminCookieName);

        if (! is_string($adminCookieName) || $adminCookieName === '') {
            $adminCookieName = 'laravel-admin-session';
        }

        if (! $this->isAdminRequest($request, $adminCookieName)) {
            return $next($request);
        }

        // Select the dedicated admin session before EncryptCookies and StartSession run.
        // Livewire temporary uploads use their own URL, so the /admin path check alone
        // is insufficient when the browser omits the Referer header. Only treat a
        // Livewire upload as admin traffic when the dedicated admin cookie is present.
        config(['session.cookie' => $adminCookieName]);

        try {
            return $next($request);
        } finally {
            config(['session.cookie' => $originalCookieName]);
        }
    }

    private function isAdminRequest(Request $request, string $adminCookieName): bool
    {
        if ($request->is('admin') || $request->is('admin/*')) {
            return true;
        }

        $referer = $request->headers->get('referer');
        if (is_string($referer) && $referer !== '') {
            $refererPath = parse_url($referer, PHP_URL_PATH);
            if (is_string($refererPath)
                && ($refererPath === '/admin' || str_starts_with($refererPath, '/admin/'))) {
                return true;
            }
        }

        // Livewire 3/4 temporary upload endpoints have the form
        // /livewire-<hash>/upload-file. Do not switch public requests merely
        // because the admin cookie exists; scope this fallback to upload requests.
        $isLivewireUpload = preg_match('#^livewire-[^/]+/upload-file$#', ltrim($request->path(), '/')) === 1;

        return $isLivewireUpload && $request->cookies->has($adminCookieName);
    }
}
