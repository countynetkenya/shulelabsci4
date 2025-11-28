<?php

namespace App\Modules\Security\Filters;

use App\Modules\Security\Services\AuthorizationService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Permission filter - checks if user has required permission.
 */
class PermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (empty($arguments)) {
            return;
        }

        $userId = session('user_id');
        $schoolId = session('school_id');

        if (!$userId) {
            return redirect()->to('/auth/signin')
                ->with('error', 'Please log in to access this resource.');
        }

        $authService = new AuthorizationService();
        $requiredPermission = $arguments[0];

        // Check for wildcard permission (e.g., 'finance.*')
        if (str_ends_with($requiredPermission, '.*')) {
            $module = rtrim($requiredPermission, '.*');
            $permissions = $authService->getUserPermissions($userId, $schoolId);
            $hasPermission = false;
            foreach ($permissions as $perm) {
                if (str_starts_with($perm, $module . '.')) {
                    $hasPermission = true;
                    break;
                }
            }
        } else {
            $hasPermission = $authService->hasPermission($userId, $requiredPermission, $schoolId);
        }

        if (!$hasPermission) {
            // For API requests, return JSON
            if ($request->isAJAX() || str_starts_with($request->getPath(), 'api/')) {
                return service('response')
                    ->setStatusCode(403)
                    ->setJSON([
                        'status' => 'error',
                        'message' => 'You do not have permission to access this resource.',
                    ]);
            }

            // For web requests, redirect with error
            return redirect()->back()
                ->with('error', 'You do not have permission to access this resource.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing to do after request
    }
}
