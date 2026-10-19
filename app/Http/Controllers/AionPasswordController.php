<?php

namespace App\Http\Controllers;

use App\Services\AionLoginService;
use App\Services\AionPasswordService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Services\AionPasswordPolicy;
use Throwable;

class AionPasswordController extends Controller
{
    public function edit(): View
    {
        return view('pages.change-password', [
            'accountUsername' => session('aion_account_username'),
        ]);
    }

    public function update(
        Request $request,
        AionLoginService $login,
        AionPasswordService $passwords,
    ): RedirectResponse {
        $username = (string) $request->session()->get('aion_account_username', '');
        $validator = Validator::make($request->all(), [
            // Existing accounts may still have a password created under the old policy.
            'current_password' => ['required', 'string', 'min:1', 'max:16'],
            'new_password' => ['required', 'string', 'min:8', 'max:16', 'confirmed', 'different:current_password'],
        ], [
            'new_password.confirmed' => 'The new password confirmation does not match.',
            'new_password.different' => 'Your new password must be different from your current password.',
        ]);
        $validator->after(function ($validator) use ($request, $username): void {
            $password = (string) $request->input('new_password', '');
            foreach (AionPasswordPolicy::errors($password, $username) as $message) {
                $validator->errors()->add('new_password', $message);
            }
        });
        $validated = $validator->validate();

        $sessionUid = $request->session()->get('aion_account_uid');

        try {
            // Re-authenticate the current password before allowing a sensitive change.
            $account = $login->attempt($username, $validated['current_password']);
            if ($account === null || ($sessionUid !== null && (int) $account->uid !== (int) $sessionUid)) {
                return back()->withErrors([
                    'current_password' => 'Your current password is incorrect.',
                ])->withInput();
            }

            $passwords->change($username, $validated['new_password']);

            // Rotate the session after changing credentials.
            $request->session()->regenerate();

            return redirect()->route('account')->with('status', 'Your AION game password has been changed successfully.');
        } catch (Throwable $exception) {
            Log::warning('AION password change failed.', [
                'username' => $username,
                'exception' => $exception::class,
            ]);

            return back()->withErrors([
                'password_change' => 'The password could not be changed. Please try again later or contact support.',
            ])->withInput();
        }
    }
}
