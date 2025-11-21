<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Guest Filter
 *
 * Redirects authenticated users away from guest-only pages (like signin)
 */
class GuestFilter implements FilterInterface
{
    /**
     * Redirect authenticated users to dashboard
     *
     * @param RequestInterface $request
     * @param array<int|string, mixed>|null $arguments
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // If user is already logged in, redirect to dashboard
        if ($session->get('loggedin')) {
            return redirect()->to('/dashboard');
        }
    }

    /**
     * After filter - not used
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array<int|string, mixed>|null $arguments
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Not needed
    }
}
