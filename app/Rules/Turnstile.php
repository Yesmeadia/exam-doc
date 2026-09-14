<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Turnstile implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Bypass in automated test suites
        if (app()->environment('testing')) {
            return;
        }

        $secretKey = config('services.turnstile.secret_key');

        // If no secret key is configured, do not block local developer workflows
        if (empty($secretKey)) {
            return;
        }

        // Must supply response token
        if (empty($value) || !is_string($value)) {
            $fail('Please complete the security challenge verification.');
            return;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => $secretKey,
                    'response' => $value,
                    'remoteip' => request()->ip(),
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['success'])) {
                    return;
                }

                Log::warning('Turnstile verification failed', [
                    'error_codes' => $data['error-codes'] ?? [],
                    'ip' => request()->ip(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Turnstile verification request error: ' . $e->getMessage());
            // Fail open in local environment if Cloudflare network is unreachable
            if (app()->isLocal()) {
                return;
            }
        }

        $fail('Security verification failed. Please retry the challenge.');
    }
}
