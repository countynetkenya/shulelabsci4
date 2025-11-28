<?php

namespace App\Modules\Security\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Security headers filter - adds security headers to all responses.
 */
class SecurityHeadersFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Nothing to do before request
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Prevent clickjacking
        $response->setHeader('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME type sniffing
        $response->setHeader('X-Content-Type-Options', 'nosniff');

        // XSS protection (legacy, but still useful)
        $response->setHeader('X-XSS-Protection', '1; mode=block');

        // Referrer policy
        $response->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        // HSTS (only in production with HTTPS)
        if (ENVIRONMENT === 'production' && isset($_SERVER['HTTPS'])) {
            $response->setHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Permissions Policy (replaces Feature-Policy)
        $response->setHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // Content Security Policy (basic)
        $csp = "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; " .
            "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; " .
            "img-src 'self' data: https:; " .
            "connect-src 'self' https://api.shule.co.ke;";

        $response->setHeader('Content-Security-Policy', $csp);

        return $response;
    }
}
