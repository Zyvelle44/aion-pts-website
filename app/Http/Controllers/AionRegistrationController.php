<?php

namespace App\Http\Controllers;

use App\Services\AionRegistrationService;
use App\Exceptions\AionRegistrationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Services\AionPasswordPolicy;
use Throwable;

class AionRegistrationController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request, AionRegistrationService $registration): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'min:4', 'max:14', 'regex:/\A[A-Za-z0-9]+\z/'],
            'email' => ['required', 'string', 'email:rfc', 'max:100'],
            'password' => ['required', 'string', 'min:8', 'max:16', 'confirmed'],
        ], [
            'username.regex' => 'Username may contain English letters and numbers only.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);
        $validator->after(function ($validator) use ($request): void {
            $password = (string) $request->input('password', '');
            $username = (string) $request->input('username', '');
            foreach (AionPasswordPolicy::errors($password, $username) as $message) {
                $validator->errors()->add('password', $message);
            }
        });
        $validated = $validator->validate();

        try {
            $registration->register(
                $validated['username'],
                $validated['password'],
                $validated['email'],
            );
        } catch (AionRegistrationException $exception) {
            return back()
                ->withInput($request->only('username', 'email'))
                ->withErrors(['registration' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            Log::error('AION game account registration failed.', [
                'username' => $validated['username'],
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return back()
                ->withInput($request->only('username', 'email'))
                ->withErrors(['registration' => 'Registration could not be completed. Please try again later or contact support.']);
        }

        return redirect()
            ->route('register')
            ->with('status', 'Your AION game account was created successfully. You can now log in.');
    }
}
