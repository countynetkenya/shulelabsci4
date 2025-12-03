<?php

namespace Modules\LMS\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Modules\LMS\Models\CourseModel;
use Modules\LMS\Models\EnrollmentModel;
use Modules\LMS\Models\LessonModel;
use Modules\LMS\Models\ProgressModel;

class LessonsApiController extends ResourceController
{
    protected $format = 'json';

    public function show($id = null)
    {
        $userId = session('user_id');
        $schoolId = session('current_school_id') ?? session('school_id');

        if (!$userId || !$schoolId) {
            return $this->failUnauthorized('User not authenticated or school context missing.');
        }

        $lessonModel = new LessonModel();
        $enrollmentModel = new EnrollmentModel();

        $lesson = $lessonModel->find($id);
        if (!$lesson) {
            return $this->failNotFound('Lesson not found.');
        }

        // Verify enrollment in the course
        $enrollment = $enrollmentModel->where('student_id', $userId)
                                      ->where('school_id', $schoolId)
                                      ->where('course_id', $lesson['course_id'])
                                      ->where('status', 'active')
                                      ->first();

        if (!$enrollment) {
            return $this->failNotFound('You are not enrolled in this course.');
        }

        return $this->respond([
            'status' => 200,
            'error' => null,
            'messages' => ['success' => 'Lesson retrieved successfully'],
            'data' => $lesson
        ]);
    }

    public function complete($id = null)
    {
        $userId = session('user_id');
        $schoolId = session('current_school_id') ?? session('school_id');

        if (!$userId || !$schoolId) {
            return $this->failUnauthorized('User not authenticated or school context missing.');
        }

        $lessonModel = new LessonModel();
        $enrollmentModel = new EnrollmentModel();
        $progressModel = new ProgressModel();

        $lesson = $lessonModel->find($id);
        if (!$lesson) {
            return $this->failNotFound('Lesson not found.');
        }

        // Verify enrollment
        $enrollment = $enrollmentModel->where('student_id', $userId)
                                      ->where('school_id', $schoolId)
                                      ->where('course_id', $lesson['course_id'])
                                      ->where('status', 'active')
                                      ->first();

        if (!$enrollment) {
            return $this->failNotFound('You are not enrolled in this course.');
        }

        // Check if already completed
        $existingProgress = $progressModel->where('enrollment_id', $enrollment['id'])
                                          ->where('lesson_id', $id)
                                          ->first();

        if ($existingProgress) {
            return $this->respond([
                'status' => 200,
                'error' => null,
                'messages' => ['success' => 'Lesson already completed'],
                'data' => []
            ]);
        }

        // Mark as complete
        $progressModel->insert([
            'enrollment_id' => $enrollment['id'],
            'lesson_id'     => $id,
            'completed_at'  => date('Y-m-d H:i:s'),
        ]);

        return $this->respond([
            'status' => 200,
            'error' => null,
            'messages' => ['success' => 'Lesson marked as complete'],
            'data' => []
        ]);
    }
}
