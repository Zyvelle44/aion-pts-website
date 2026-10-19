<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAionAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $username = $request->session()->get('aion_account_username');
        $uid = $request->session()->get('aion_account_uid');

        // Match the protected-route middleware: incomplete session data is not
        // treated as an authenticated game account.
        if (is_string($username) && $username !== '' && is_numeric($uid) && (int) $uid > 0) {
            return redirect()->route('account');
        }

        if ($request->session()->has('aion_account_username') || $request->session()->has('aion_account_uid')) {
            $request->session()->forget([
                'aion_account_username',
                'aion_account_email',
                'aion_account_uid',
            ]);
        }

        return $next($request);
    }
}
