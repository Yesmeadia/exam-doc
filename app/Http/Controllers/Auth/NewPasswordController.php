<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $emailKey = 'reset_pass_user:' . Str::transliterate(Str::lower($request->string('email')));
        $ipKey = 'reset_pass_ip:' . $request->ip();

        // 1. User-based rate limit (5 attempts per 5 minutes)
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($emailKey, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($emailKey);
            throw ValidationException::withMessages([
                'email' => "Too many password reset attempts for this email. Please wait {$seconds} seconds before trying again.",
            ]);
        }

        // 2. IP-based rate limit (10 attempts per 5 minutes)
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($ipKey, 10)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($ipKey);
            throw ValidationException::withMessages([
                'email' => "Too many password reset attempts from this network. Please wait {$seconds} seconds before trying again.",
            ]);
        }

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            \Illuminate\Support\Facades\RateLimiter::clear($emailKey);
            \Illuminate\Support\Facades\RateLimiter::clear($ipKey);

            return redirect()->route('login')->with('status', __($status));
        }

        \Illuminate\Support\Facades\RateLimiter::hit($emailKey, 300);
        \Illuminate\Support\Facades\RateLimiter::hit($ipKey, 300);

        return back()->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}
