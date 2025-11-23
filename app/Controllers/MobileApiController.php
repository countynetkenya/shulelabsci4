<?php

namespace App\Controllers;

use App\Services\MobileApiService;
use App\Services\TenantService;

/**
 * MobileApiController - Mobile-first API endpoints.
 */
class MobileApiController extends BaseController
{
    protected MobileApiService $mobileService;

    protected TenantService $tenantService;

    public function __construct()
    {
        $this->mobileService = new MobileApiService();
        $this->tenantService = service('tenant');
    }

    /**
     * Get dashboard (mobile).
     */
    public function dashboard()
    {
        $schoolId = $this->request->getGet('school_id') ?? $this->tenantService->getCurrentSchoolId();

        if (!$schoolId) {
            return $this->response->setJSON(
                $this->mobileService->formatResponse(false, null, 'School ID required', 400)
            )->setStatusCode(400);
        }

        $data = $this->mobileService->getDashboard($schoolId);
        return $this->response->setJSON($data);
    }

    /**
     * Get student profile (mobile).
     */
    public function studentProfile(int $studentId)
    {
        $schoolId = $this->request->getGet('school_id') ?? $this->tenantService->getCurrentSchoolId();

        if (!$schoolId) {
            return $this->response->setJSON(
                $this->mobileService->formatResponse(false, null, 'School ID required', 400)
            )->setStatusCode(400);
        }

        $data = $this->mobileService->getStudentProfile($studentId, $schoolId);
        return $this->response->setJSON($data);
    }

    /**
     * Get class students (mobile).
     */
    public function classStudents(int $classId)
    {
        $schoolId = $this->request->getGet('school_id') ?? $this->tenantService->getCurrentSchoolId();
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = (int) ($this->request->getGet('per_page') ?? 20);

        if (!$schoolId) {
            return $this->response->setJSON(
                $this->mobileService->formatResponse(false, null, 'School ID required', 400)
            )->setStatusCode(400);
        }

        $data = $this->mobileService->getClassStudents($classId, $schoolId, $page, $perPage);
        return $this->response->setJSON($data);
    }

    /**
     * Get invoices (mobile).
     */
    public function invoices()
    {
        $schoolId = $this->request->getGet('school_id') ?? $this->tenantService->getCurrentSchoolId();
        $status = $this->request->getGet('status');
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = (int) ($this->request->getGet('per_page') ?? 20);

        if (!$schoolId) {
            return $this->response->setJSON(
                $this->mobileService->formatResponse(false, null, 'School ID required', 400)
            )->setStatusCode(400);
        }

        $data = $this->mobileService->getInvoices($schoolId, $status, $page, $perPage);
        return $this->response->setJSON($data);
    }

    /**
     * Get library books (mobile).
     */
    public function libraryBooks()
    {
        $schoolId = $this->request->getGet('school_id') ?? $this->tenantService->getCurrentSchoolId();
        $category = $this->request->getGet('category');
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = (int) ($this->request->getGet('per_page') ?? 20);

        if (!$schoolId) {
            return $this->response->setJSON(
                $this->mobileService->formatResponse(false, null, 'School ID required', 400)
            )->setStatusCode(400);
        }

        $data = $this->mobileService->getLibraryBooks($schoolId, $category, $page, $perPage);
        return $this->response->setJSON($data);
    }

    /**
     * Get courses (mobile).
     */
    public function courses()
    {
        $schoolId = $this->request->getGet('school_id') ?? $this->tenantService->getCurrentSchoolId();
        $classId = $this->request->getGet('class_id') ? (int) $this->request->getGet('class_id') : null;

        if (!$schoolId) {
            return $this->response->setJSON(
                $this->mobileService->formatResponse(false, null, 'School ID required', 400)
            )->setStatusCode(400);
        }

        $data = $this->mobileService->getCourses($schoolId, $classId);
        return $this->response->setJSON($data);
    }

    /**
     * Get student grades (mobile).
     */
    public function studentGrades(int $studentId, int $courseId)
    {
        $data = $this->mobileService->getStudentGrades($studentId, $courseId);
        return $this->response->setJSON($data);
    }
}
