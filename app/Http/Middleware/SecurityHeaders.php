<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request and attach standard OWASP security headers.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Clickjacking protection: only allow same origin framing
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevent browser MIME-sniffing away from declared Content-Type
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Legacy browser XSS protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer policy: limit sensitive URL leakage on outbound navigation
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Restrict powerful browser hardware APIs
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // HSTS: enforce HTTPS if served over SSL
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
