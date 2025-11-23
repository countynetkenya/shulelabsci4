<?php

namespace App\Controllers;

use App\Services\SchoolService;
use App\Services\TenantService;

/**
 * SchoolController - Manage schools and multi-tenant operations.
 */
class SchoolController extends BaseController
{
    protected SchoolService $schoolService;
    protected TenantService $tenantService;

    public function __construct()
    {
        $this->schoolService = new SchoolService();
        $this->tenantService = service('tenant');
    }

    /**
     * Display school dashboard.
     */
    public function dashboard()
    {
        $schoolId = $this->tenantService->getCurrentSchoolId();
        
        if (!$schoolId) {
            return redirect()->to('/school/select')->with('error', 'Please select a school');
        }

        $data = $this->schoolService->getDashboardStats($schoolId);

        return view('school/dashboard', $data);
    }

    /**
     * Display school overview with detailed statistics.
     */
    public function overview()
    {
        $schoolId = $this->tenantService->getCurrentSchoolId();
        
        if (!$schoolId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No school context selected',
            ])->setStatusCode(400);
        }

        $data = $this->schoolService->getSchoolOverview($schoolId);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'data' => $data]);
        }

        return view('school/overview', $data);
    }

    /**
     * Get school statistics (API endpoint).
     */
    public function stats()
    {
        $schoolId = $this->tenantService->getCurrentSchoolId();
        
        if (!$schoolId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No school context selected',
            ])->setStatusCode(400);
        }

        $stats = $this->schoolService->getDashboardStats($schoolId);

        return $this->response->setJSON([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Check enrollment capacity (API endpoint).
     */
    public function checkCapacity()
    {
        $schoolId = $this->tenantService->getCurrentSchoolId();
        $count = $this->request->getGet('count') ?? 1;

        if (!$schoolId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No school context selected',
            ])->setStatusCode(400);
        }

        $canEnroll = $this->schoolService->canEnrollStudents($schoolId, (int)$count);

        return $this->response->setJSON([
            'success' => true,
            'can_enroll' => $canEnroll,
            'count' => (int)$count,
        ]);
    }
}
