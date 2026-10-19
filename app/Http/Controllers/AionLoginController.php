<?php

namespace App\Http\Controllers;

use App\Services\AionLoginService;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class AionLoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request, AionLoginService $login): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'min:4', 'max:14', 'regex:/^[A-Za-z0-9]+$/'],
            // Keep legacy login compatibility for accounts created before the new
            // password policy. New passwords are enforced at registration/change.
            'password' => ['required', 'string', 'min:1', 'max:16'],
        ], [
            'username.regex' => 'Enter your game username using English letters and numbers only.',
        ]);

        try {
            $account = $login->attempt($validated['username'], $validated['password']);
        } catch (Throwable $exception) {
            Log::warning('AION login query failed.', [
                'username' => $validated['username'],
                'exception' => $exception::class,
            ]);

            return back()
                ->withInput($request->only('username'))
                ->withErrors(['login' => 'Login is temporarily unavailable. Please try again later.']);
        }

        if ($account === null || ! isset($account->uid) || (int) $account->uid <= 0) {
            return back()
                ->withInput($request->only('username'))
                ->withErrors(['login' => 'The username or password is incorrect.']);
        }

        $request->session()->regenerate();
        $request->session()->put('aion_account_username', (string) $account->account);
        $request->session()->put('aion_account_email', (string) ($account->email ?? ''));
        $request->session()->put('aion_account_uid', (int) $account->uid);

        return redirect()->route('account')->with('status', 'You are now signed in to your AION game account.');
    }

    public function account(Request $request): View
    {
        $username = (string) $request->session()->get('aion_account_username', '');
        $profile = null;

        // Read only account metadata from the existing AION SQL Server schema.
        // Never expose password hashes, authentication answers, or IP addresses.
        try {
            $profile = \Illuminate\Support\Facades\DB::connection(
                (string) config('aion_registration.connection', 'aion_game')
            )->selectOne(
                'SELECT TOP (1) '
                . 'wa.uid AS account_uid, wa.create_date AS account_created_at, '
                . 'wa.donate_coin AS donate_coin, wa.login_flag_web AS web_login_flag, '
                . 'ua.pay_stat AS pay_status, ua.block_flag AS block_flag, '
                . 'ua.block_flag2 AS secondary_block_flag, ua.subscription_flag AS subscription_flag, '
                . 'ua.last_login AS last_login, ua.last_logout AS last_logout, ua.last_world AS last_world '
                . 'FROM dbo.web_account AS wa '
                . 'LEFT JOIN dbo.user_account AS ua ON ua.account = wa.account '
                . 'WHERE wa.account = ? ORDER BY wa.row_id DESC',
                [$username],
            );
        } catch (\Throwable $exception) {
            // The dashboard still renders with session-backed identity if metadata is unavailable.
            \Illuminate\Support\Facades\Log::warning('AION account profile metadata query failed.', [
                'exception' => $exception::class,
            ]);
        }

        return view('pages.account', [
            'accountUsername' => $username,
            'accountEmail' => $request->session()->get('aion_account_email', ''),
            'accountProfile' => $profile,
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        // Remove only game-account identity. Do not invalidate the whole
        // public session or affect the separately-cookie-isolated admin session.
        $request->session()->forget([
            'aion_account_username',
            'aion_account_email',
            'aion_account_uid',
        ]);

        return redirect()->route('home')->with('status', 'You have been logged out.');
    }
}
