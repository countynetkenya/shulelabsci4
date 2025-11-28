<?php

namespace App\Modules\Security\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Modules\Security\Services\RateLimiterService;

/**
 * Rate limiting filter for API endpoints.
 */
class RateLimitFilter implements FilterInterface
{
    private int $maxRequests = 100;
    private int $decaySeconds = 60;

    public function before(RequestInterface $request, $arguments = null)
    {
        // Parse arguments for custom limits
        if ($arguments) {
            $this->maxRequests = isset($arguments[0]) ? (int) $arguments[0] : $this->maxRequests;
            $this->decaySeconds = isset($arguments[1]) ? (int) $arguments[1] : $this->decaySeconds;
        }

        $limiter = new RateLimiterService();

        // Determine rate limit key
        $userId = session('user_id');
        $ip = $request->getIPAddress();
        $key = $userId
            ? RateLimiterService::apiKey((string) $userId)
            : RateLimiterService::ipKey($ip, 'api');

        // Check rate limit
        $result = $limiter->check($key, $this->maxRequests, $this->decaySeconds);

        if (!$result['allowed']) {
            $response = service('response');
            return $response
                ->setStatusCode(429)
                ->setHeader('Retry-After', (string) $result['retry_after'])
                ->setHeader('X-RateLimit-Limit', (string) $this->maxRequests)
                ->setHeader('X-RateLimit-Remaining', '0')
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Too many requests. Please try again later.',
                    'retry_after' => $result['retry_after'],
                ]);
        }

        // Record the hit
        $limiter->hit($key, $this->decaySeconds);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Add rate limit headers to response
        $limiter = new RateLimiterService();
        $userId = session('user_id');
        $ip = $request->getIPAddress();
        $key = $userId
            ? RateLimiterService::apiKey((string) $userId)
            : RateLimiterService::ipKey($ip, 'api');

        $remaining = $limiter->remaining($key, $this->maxRequests);

        return $response
            ->setHeader('X-RateLimit-Limit', (string) $this->maxRequests)
            ->setHeader('X-RateLimit-Remaining', (string) $remaining);
    }
}
