<?php

namespace Modules\Learning\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Modules\Learning\Services\LearningService;

class CoursesController extends ResourceController
{
    protected $learningService;

    public function __construct()
    {
        $this->learningService = new LearningService();
    }

    public function index()
    {
        $schoolId = $this->request->getVar('school_id'); // In real app, get from token
        
        if (!$schoolId) {
            return $this->fail('School ID is required');
        }

        $courses = $this->learningService->getSchoolCourses($schoolId, 'published');
        return $this->respond($courses);
    }

    public function show($id = null)
    {
        $course = $this->learningService->getCourse($id);
        if (!$course) {
            return $this->failNotFound('Course not found');
        }

        $lessons = $this->learningService->getCourseLessons($id);
        $course['lessons'] = $lessons;

        return $this->respond($course);
    }
}
