<?php

namespace Modules\LMS\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Modules\LMS\Models\CourseModel;
use Modules\LMS\Models\EnrollmentModel;

class CoursesApiController extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $userId = session('user_id');
        $schoolId = session('current_school_id') ?? session('school_id');

        if (!$userId || !$schoolId) {
            return $this->failUnauthorized('User not authenticated or school context missing.');
        }

        $enrollmentModel = new EnrollmentModel();
        $courseModel = new CourseModel();

        // Get enrolled courses
        $enrollments = $enrollmentModel->where('student_id', $userId)
                                       ->where('school_id', $schoolId)
                                       ->where('status', 'active')
                                       ->findAll();

        if (empty($enrollments)) {
            return $this->respond([
                'status' => 200,
                'error' => null,
                'messages' => ['success' => 'No courses found'],
                'data' => [],
            ]);
        }

        $courseIds = array_column($enrollments, 'course_id');
        $courses = $courseModel->whereIn('id', $courseIds)
                               ->where('status', 'published')
                               ->findAll();

        return $this->respond([
            'status' => 200,
            'error' => null,
            'messages' => ['success' => 'Courses retrieved successfully'],
            'data' => $courses,
        ]);
    }

    public function show($id = null)
    {
        $userId = session('user_id');
        $schoolId = session('current_school_id') ?? session('school_id');

        if (!$userId || !$schoolId) {
            return $this->failUnauthorized('User not authenticated or school context missing.');
        }

        $enrollmentModel = new EnrollmentModel();
        $courseModel = new CourseModel();
        $lessonModel = new \Modules\LMS\Models\LessonModel();

        // Verify enrollment
        $enrollment = $enrollmentModel->where('student_id', $userId)
                                      ->where('school_id', $schoolId)
                                      ->where('course_id', $id)
                                      ->where('status', 'active')
                                      ->first();

        if (!$enrollment) {
            return $this->failNotFound('Course not found or not enrolled.');
        }

        $course = $courseModel->find($id);
        if (!$course || $course['status'] !== 'published') {
            return $this->failNotFound('Course not found.');
        }

        // Get lessons
        $lessons = $lessonModel->where('course_id', $id)
                               ->orderBy('sequence_order', 'ASC')
                               ->findAll();

        $course['lessons'] = $lessons;

        return $this->respond([
            'status' => 200,
            'error' => null,
            'messages' => ['success' => 'Course details retrieved successfully'],
            'data' => $course,
        ]);
    }
}
