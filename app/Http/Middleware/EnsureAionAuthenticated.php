<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAionAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $username = $request->session()->get('aion_account_username');
        $uid = $request->session()->get('aion_account_uid');

        // A valid authenticated session must have both the game username and UID.
        if (!is_string($username) || $username === '' || !is_numeric($uid) || (int) $uid <= 0) {
            $request->session()->forget([
                'aion_account_username',
                'aion_account_email',
                'aion_account_uid',
            ]);

            return redirect()->route('login');
        }

        return $next($request);
    }
}
