<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        // Chi set cho response HTTP thong thuong (khong phai binary/stream dac biet)
        if (!$response->headers->has('X-Frame-Options')) {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        }

        if (!$response->headers->has('X-Content-Type-Options')) {
            $response->headers->set('X-Content-Type-Options', 'nosniff');
        }

        if (!$response->headers->has('Referrer-Policy')) {
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        }

        if (!$response->headers->has('Permissions-Policy')) {
            // Tat cac quyen nhay cam, se bat tung cai neu can ve sau
            $response->headers->set('Permissions-Policy', 'accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()');
        }

        if (!$response->headers->has('X-XSS-Protection')) {
            // Cho trinh duyet cu, trinh duyet moi bo qua header nay
            $response->headers->set('X-XSS-Protection', '1; mode=block');
        }

        // Content-Security-Policy an toan mac dinh cho Inertia + React
        if (!$response->headers->has('Content-Security-Policy')) {
            // Allow Vite dev server in local/development environment
            $viteHost = app()->environment(['local', 'development'])
                ? 'http://127.0.0.1:5173 http://localhost:5173 ws://127.0.0.1:5173 ws://localhost:5173'
                : '';

            $csp = implode('; ', array_filter([
                "default-src 'self'" . ($viteHost ? " $viteHost" : ''),
                "script-src 'self' 'unsafe-inline' 'unsafe-eval'" . ($viteHost ? " $viteHost" : ''),
                "style-src 'self' 'unsafe-inline'" . ($viteHost ? " $viteHost" : ''),
                "img-src 'self' data: https:",
                "font-src 'self' data:" . ($viteHost ? " $viteHost" : ''),
                "connect-src 'self'" . ($viteHost ? " $viteHost" : ''),
                "frame-ancestors 'self'",
                "base-uri 'self'",
                "form-action 'self'",
            ]));

            $response->headers->set('Content-Security-Policy', $csp);
        }

        return $response;
    }
}


