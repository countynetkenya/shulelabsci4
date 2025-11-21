<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Auth Filter
 *
 * Ensures user is authenticated before accessing protected routes
 */
class AuthFilter implements FilterInterface
{
    /**
     * Check if user is logged in
     *
     * @param RequestInterface $request
     * @param array<int|string, mixed>|null $arguments
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Check if user is logged in
        if (!$session->get('loggedin')) {
            // Store intended URL to redirect back after login
            $session->set('redirect_url', current_url());

            // Redirect to signin page
            return redirect()->to('/auth/signin');
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
