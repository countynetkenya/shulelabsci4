<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if ($session->get('isLoggedIn')) {
            return null;
        }

        if ($request->isAJAX()) {
            return service('response')
                ->setJSON(['error' => 'Authentication required.'])
                ->setStatusCode(401);
        }

        return redirect()->route('login')->with('error', 'Please sign in to continue.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing to do after the request.
    }
}
