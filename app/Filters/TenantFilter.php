<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * TenantFilter - Ensures tenant context is set and user has access.
 *
 * This filter should be applied to all routes that require tenant context.
 */
class TenantFilter implements FilterInterface
{
    /**
     * Set tenant context before request.
     *
     * @param array|null $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $tenantService = service('tenant');
        $tenantService->setCurrentSchool();

        // Verify user has access to current school
        $session = session();
        if ($session->has('user_id') && $tenantService->getCurrentSchoolId()) {
            $userId = (int) $session->get('user_id');
            $schoolId = $tenantService->getCurrentSchoolId();

            // Check if user has access to this school
            if (!$tenantService->hasAccessToSchool($userId, $schoolId)) {
                // User doesn't have access - redirect to school selector
                return redirect()->to('/dashboard/select-school')
                    ->with('error', 'You do not have access to this school. Please select a school you have access to.');
            }
        }

        // If no school context and user is logged in, redirect to selector
        if ($session->has('user_id') && !$tenantService->getCurrentSchoolId()) {
            $schools = $tenantService->getUserSchools((int) $session->get('user_id'));

            if (empty($schools)) {
                return redirect()->to('/dashboard')
                    ->with('error', 'You are not assigned to any school. Please contact your administrator.');
            }

            // If user has only one school, auto-select it
            if (count($schools) === 1) {
                $tenantService->switchSchool($schools[0]['id'], (int) $session->get('user_id'));

                return redirect()->to($request->getUri());
            }

            return redirect()->to('/dashboard/select-school');
        }
    }

    /**
     * No action needed after request.
     *
     * @param array|null $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed
    }
}
