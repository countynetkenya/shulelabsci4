<?php

namespace App\Controllers;

use App\Services\HrService;
use App\Services\TenantService;

/**
 * HrController - Human resources management.
 */
class HrController extends BaseController
{
    protected HrService $hrService;
    protected TenantService $tenantService;

    public function __construct()
    {
        $this->hrService = new HrService();
        $this->tenantService = service('tenant');
    }

    /**
     * Display staff list.
     */
    public function staff()
    {
        $schoolId = $this->tenantService->getCurrentSchoolId();
        
        if (!$schoolId) {
            return redirect()->to('/school/select');
        }

        $roleFilter = $this->request->getGet('role');
        $staff = $this->hrService->getSchoolStaff($schoolId, $roleFilter);
        $stats = $this->hrService->getStaffStats($schoolId);

        return view('hr/staff', [
            'staff' => $staff,
            'stats' => $stats,
            'school_id' => $schoolId,
        ]);
    }

    /**
     * Assign teacher to school (API).
     */
    public function assignTeacher()
    {
        $schoolId = $this->tenantService->getCurrentSchoolId();
        $userId = $this->request->getPost('user_id');
        $isPrimary = $this->request->getPost('is_primary') === '1';

        if (!$schoolId || !$userId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request',
            ])->setStatusCode(400);
        }

        $result = $this->hrService->assignTeacher((int)$userId, $schoolId, $isPrimary);

        return $this->response->setJSON($result);
    }

    /**
     * Remove teacher from school (API).
     */
    public function removeTeacher()
    {
        $schoolId = $this->tenantService->getCurrentSchoolId();
        $userId = $this->request->getPost('user_id');

        if (!$schoolId || !$userId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request',
            ])->setStatusCode(400);
        }

        $result = $this->hrService->removeTeacher((int)$userId, $schoolId);

        return $this->response->setJSON($result);
    }

    /**
     * Get teacher's classes (API).
     */
    public function teacherClasses(int $teacherId)
    {
        $schoolId = $this->tenantService->getCurrentSchoolId();

        if (!$schoolId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No school context',
            ])->setStatusCode(400);
        }

        $classes = $this->hrService->getTeacherClasses($teacherId, $schoolId);

        return $this->response->setJSON([
            'success' => true,
            'classes' => $classes,
        ]);
    }

    /**
     * Assign teacher to class (API).
     */
    public function assignToClass()
    {
        $schoolId = $this->tenantService->getCurrentSchoolId();
        $classId = $this->request->getPost('class_id');
        $teacherId = $this->request->getPost('teacher_id');

        if (!$schoolId || !$classId || !$teacherId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request',
            ])->setStatusCode(400);
        }

        $result = $this->hrService->assignTeacherToClass((int)$classId, (int)$teacherId, $schoolId);

        return $this->response->setJSON($result);
    }

    /**
     * Get staff statistics (API).
     */
    public function stats()
    {
        $schoolId = $this->tenantService->getCurrentSchoolId();

        if (!$schoolId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No school context',
            ])->setStatusCode(400);
        }

        $stats = $this->hrService->getStaffStats($schoolId);

        return $this->response->setJSON([
            'success' => true,
            'stats' => $stats,
        ]);
    }
}
