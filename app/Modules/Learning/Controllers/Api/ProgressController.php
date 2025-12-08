<?php

namespace Modules\Learning\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Modules\Learning\Services\LearningService;

class ProgressController extends ResourceController
{
    protected $learningService;

    public function __construct()
    {
        $this->learningService = new LearningService();
    }

    public function create()
    {
        $rules = [
            'enrollment_id' => 'required|integer',
            'lesson_id' => 'required|integer'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $enrollmentId = $this->request->getVar('enrollment_id');
        $lessonId = $this->request->getVar('lesson_id');

        // TODO: Verify that the enrollment belongs to the current student

        if ($this->learningService->markLessonComplete($enrollmentId, $lessonId)) {
            return $this->respondCreated([
                'status' => 201,
                'message' => 'Lesson marked as complete'
            ]);
        }

        return $this->fail('Failed to update progress');
    }

    public function show($enrollmentId = null)
    {
        if (!$enrollmentId) {
            return $this->failNotFound('Enrollment ID required');
        }

        $progress = $this->learningService->getCompletedLessons($enrollmentId);

        return $this->respond([
            'status' => 200,
            'data' => $progress
        ]);
    }
}
