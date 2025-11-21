<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Admin Filter
 *
 * Restricts access to admin-only routes
 */
class AdminFilter implements FilterInterface
{
    /**
     * Check if user is admin
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
            return redirect()->to('/auth/signin');
        }

        $usertypeID = $session->get('usertypeID');
        $loginuserID = $session->get('loginuserID');

        // Check if user is admin (usertypeID 1) or super admin (usertypeID 0)
        $isAdmin = $usertypeID === 1 || $usertypeID === '1' ||
                   $usertypeID === 0 || $usertypeID === '0';

        if (!$isAdmin) {
            return redirect()->to('/dashboard')->with('error', 'Access denied. Admin privileges required.');
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
