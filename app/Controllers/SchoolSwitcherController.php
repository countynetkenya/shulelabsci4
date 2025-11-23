<?php

namespace App\Controllers;

use App\Services\TenantService;

/**
 * SchoolSwitcherController - Handle school selection and switching.
 */
class SchoolSwitcherController extends BaseController
{
    protected TenantService $tenantService;

    public function __construct()
    {
        $this->tenantService = service('tenant');
    }

    /**
     * Display school selector page.
     */
    public function index()
    {
        $userId = auth()->id();
        
        if (!$userId) {
            return redirect()->to('/login');
        }

        $schools = $this->tenantService->getUserSchools($userId);

        if (empty($schools)) {
            return view('school/no_access', ['message' => 'You do not have access to any schools']);
        }

        // If user has only one school, auto-select it
        if (count($schools) === 1) {
            $this->tenantService->switchSchool($schools[0]['id'], $userId);
            return redirect()->to('/dashboard');
        }

        return view('school/selector', ['schools' => $schools]);
    }

    /**
     * Switch to selected school.
     */
    public function switch()
    {
        $userId = auth()->id();
        $schoolId = $this->request->getPost('school_id');

        if (!$userId || !$schoolId) {
            return redirect()->back()->with('error', 'Invalid request');
        }

        $success = $this->tenantService->switchSchool((int)$schoolId, $userId);

        if (!$success) {
            return redirect()->back()->with('error', 'You do not have access to this school');
        }

        return redirect()->to('/dashboard')->with('success', 'School switched successfully');
    }

    /**
     * API endpoint to switch school.
     */
    public function apiSwitch()
    {
        $userId = auth()->id();
        $schoolId = $this->request->getJSON()->school_id ?? null;

        if (!$userId || !$schoolId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request',
            ])->setStatusCode(400);
        }

        $success = $this->tenantService->switchSchool((int)$schoolId, $userId);

        if (!$success) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You do not have access to this school',
            ])->setStatusCode(403);
        }

        $schoolData = model('SchoolModel')->find($schoolId);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'School switched successfully',
            'school' => $schoolData,
        ]);
    }

    /**
     * Get available schools for current user (API).
     */
    public function getSchools()
    {
        $userId = auth()->id();

        if (!$userId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized',
            ])->setStatusCode(401);
        }

        $schools = $this->tenantService->getUserSchools($userId);

        return $this->response->setJSON([
            'success' => true,
            'schools' => $schools,
            'current_school_id' => $this->tenantService->getCurrentSchoolId(),
        ]);
    }
}
