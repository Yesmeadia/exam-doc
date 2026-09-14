<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'cf-turnstile-response' => [new \App\Rules\Turnstile()],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->userThrottleKey(), 60);
            RateLimiter::hit($this->ipThrottleKey(), 60);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $user = Auth::user();
        if ($user && in_array($user->status, ['banned', 'inactive'])) {
            Auth::logout();
            $this->session()->invalidate();
            $this->session()->regenerateToken();

            $msg = $user->status === 'banned'
                ? 'Your account has been banned by the administration. Portal access is suspended.'
                : 'Your account is currently inactive. Please contact the administrator.';

            throw ValidationException::withMessages([
                'email' => $msg,
            ]);
        }

        RateLimiter::clear($this->userThrottleKey());
        RateLimiter::clear($this->ipThrottleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        // 1. User-based rate limiting (5 attempts per minute per account)
        if (RateLimiter::tooManyAttempts($this->userThrottleKey(), 5)) {
            event(new Lockout($this));
            $seconds = RateLimiter::availableIn($this->userThrottleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        // 2. IP-based rate limiting (10 attempts per minute per IP address)
        if (RateLimiter::tooManyAttempts($this->ipThrottleKey(), 10)) {
            event(new Lockout($this));
            $seconds = RateLimiter::availableIn($this->ipThrottleKey());

            throw ValidationException::withMessages([
                'email' => "Too many login attempts from this network location. Please try again in {$seconds} seconds.",
            ]);
        }
    }

    /**
     * Get the user-based throttle key (per email).
     */
    public function userThrottleKey(): string
    {
        return 'login_user:' . Str::transliterate(Str::lower($this->string('email')));
    }

    /**
     * Get the IP-based throttle key (per IP address).
     */
    public function ipThrottleKey(): string
    {
        return 'login_ip:' . $this->ip();
    }

    /**
     * Combined fallback throttle key.
     */
    public function throttleKey(): string
    {
        return $this->userThrottleKey();
    }
}
