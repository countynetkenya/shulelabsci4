<?php

namespace Modules\Learning\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Modules\Learning\Services\LearningService;

class EnrollmentsController extends ResourceController
{
    protected $learningService;

    public function __construct()
    {
        $this->learningService = new LearningService();
    }

    public function index()
    {
        // Assuming student is authenticated and we have their ID
        // For now, we'll use a placeholder or get from session/token if available
        // In a real API, this would come from the JWT token
        $studentId = session()->get('student_id') ?? 1; // Fallback for dev/testing

        $enrollments = $this->learningService->getStudentEnrollments($studentId);

        return $this->respond([
            'status' => 200,
            'data' => $enrollments,
        ]);
    }

    public function create()
    {
        $rules = [
            'course_id' => 'required|integer',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $courseId = $this->request->getVar('course_id');
        $studentId = session()->get('student_id') ?? 1; // Fallback
        $schoolId = session()->get('current_school_id') ?? 1; // Fallback

        $enrollmentId = $this->learningService->enrollStudent($schoolId, $studentId, $courseId);

        if ($enrollmentId) {
            return $this->respondCreated([
                'status' => 201,
                'message' => 'Enrolled successfully',
                'id' => $enrollmentId,
            ]);
        }

        return $this->fail('Failed to enroll');
    }
}
