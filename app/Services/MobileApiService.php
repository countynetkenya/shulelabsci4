<?php

namespace App\Services;

/**
 * MobileApiService - Mobile-first API responses.
 */
class MobileApiService
{
    protected TenantService $tenantService;

    public function __construct()
    {
        $this->tenantService = service('tenant');
    }

    /**
     * Format API response for mobile.
     */
    public function formatResponse(bool $success, $data = null, ?string $message = null, int $statusCode = 200): array
    {
        $response = [
            'success' => $success,
            'timestamp' => date('Y-m-d H:i:s'),
            'status_code' => $statusCode,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        return $response;
    }

    /**
     * Format pagination metadata.
     */
    public function formatPagination(int $total, int $page, int $perPage): array
    {
        return [
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage),
            'has_more' => ($page * $perPage) < $total,
        ];
    }

    /**
     * Get dashboard data for mobile.
     */
    public function getDashboard(int $schoolId): array
    {
        $schoolService = new \App\Services\SchoolService();
        $stats = $schoolService->getDashboardStats($schoolId);

        return $this->formatResponse(true, [
            'school_id' => $schoolId,
            'statistics' => $stats,
            'quick_actions' => [
                ['id' => 'view_students', 'label' => 'View Students', 'icon' => 'users'],
                ['id' => 'view_classes', 'label' => 'View Classes', 'icon' => 'book'],
                ['id' => 'view_invoices', 'label' => 'View Invoices', 'icon' => 'money'],
                ['id' => 'view_library', 'label' => 'Library', 'icon' => 'library'],
            ],
        ]);
    }

    /**
     * Get student profile for mobile.
     */
    public function getStudentProfile(int $studentId, int $schoolId): array
    {
        $enrollmentService = new \App\Services\EnrollmentService();
        $enrollments = $enrollmentService->getStudentEnrollments($studentId, $schoolId);

        if (empty($enrollments)) {
            return $this->formatResponse(false, null, 'Student not found', 404);
        }

        $enrollment = $enrollments[0];

        return $this->formatResponse(true, [
            'student_id' => $studentId,
            'school_id' => $schoolId,
            'class' => $enrollment['grade_level'] ?? 'N/A',
            'enrollment_status' => $enrollment['status'] ?? 'active',
            'enrolled_date' => $enrollment['enrolled_at'] ?? null,
        ]);
    }

    /**
     * Get class students for mobile.
     */
    public function getClassStudents(int $classId, int $schoolId, int $page = 1, int $perPage = 20): array
    {
        $enrollmentService = new \App\Services\EnrollmentService();
        $students = $enrollmentService->getClassEnrollments($classId);

        $total = count($students);
        $offset = ($page - 1) * $perPage;
        $paginatedStudents = array_slice($students, $offset, $perPage);

        return $this->formatResponse(true, [
            'students' => $paginatedStudents,
            'pagination' => $this->formatPagination($total, $page, $perPage),
        ]);
    }

    /**
     * Get invoices for mobile.
     */
    public function getInvoices(int $schoolId, ?string $status = null, int $page = 1, int $perPage = 20): array
    {
        $financeService = new \App\Services\FinanceService();
        $invoices = $financeService->getSchoolInvoices($schoolId, $status);

        $total = count($invoices);
        $offset = ($page - 1) * $perPage;
        $paginatedInvoices = array_slice($invoices, $offset, $perPage);

        // Simplify invoice data for mobile
        $mobileInvoices = array_map(function ($invoice) {
            return [
                'id' => $invoice['id'],
                'student_id' => $invoice['student_id'],
                'total_amount' => $invoice['total_amount'],
                'balance' => $invoice['balance'],
                'status' => $invoice['status'],
                'due_date' => $invoice['due_date'],
            ];
        }, $paginatedInvoices);

        return $this->formatResponse(true, [
            'invoices' => $mobileInvoices,
            'pagination' => $this->formatPagination($total, $page, $perPage),
        ]);
    }

    /**
     * Get library books for mobile.
     */
    public function getLibraryBooks(int $schoolId, ?string $category = null, int $page = 1, int $perPage = 20): array
    {
        $libraryService = new \App\Services\LibraryService();
        $books = $libraryService->getSchoolBooks($schoolId, $category);

        $total = count($books);
        $offset = ($page - 1) * $perPage;
        $paginatedBooks = array_slice($books, $offset, $perPage);

        // Simplify book data for mobile
        $mobileBooks = array_map(function ($book) {
            return [
                'id' => $book['id'],
                'title' => $book['title'],
                'author' => $book['author'],
                'category' => $book['category'],
                'available' => $book['available_copies'],
                'total' => $book['total_copies'],
            ];
        }, $paginatedBooks);

        return $this->formatResponse(true, [
            'books' => $mobileBooks,
            'pagination' => $this->formatPagination($total, $page, $perPage),
        ]);
    }

    /**
     * Get courses for mobile.
     */
    public function getCourses(int $schoolId, ?int $classId = null): array
    {
        $learningService = new \App\Services\LearningService();
        $courses = $learningService->getSchoolCourses($schoolId, $classId);

        // Simplify course data for mobile
        $mobileCourses = array_map(function ($course) {
            return [
                'id' => $course['id'],
                'name' => $course['course_name'],
                'code' => $course['course_code'],
                'class_id' => $course['class_id'],
            ];
        }, $courses);

        return $this->formatResponse(true, ['courses' => $mobileCourses]);
    }

    /**
     * Get student grades for mobile.
     */
    public function getStudentGrades(int $studentId, int $courseId): array
    {
        $learningService = new \App\Services\LearningService();
        $grades = $learningService->getStudentGrades($studentId, $courseId);
        $average = $learningService->getCourseAverage($studentId, $courseId);

        // Simplify grade data
        $mobileGrades = array_map(function ($item) {
            return [
                'assignment' => $item['assignment']['title'],
                'due_date' => $item['assignment']['due_date'],
                'points_earned' => $item['grade']['points_earned'] ?? null,
                'max_points' => $item['assignment']['max_points'],
                'percentage' => $item['grade'] ? round(($item['grade']['points_earned'] / $item['grade']['max_points']) * 100, 1) : null,
            ];
        }, $grades);

        return $this->formatResponse(true, [
            'grades' => $mobileGrades,
            'course_average' => $average,
        ]);
    }
}
