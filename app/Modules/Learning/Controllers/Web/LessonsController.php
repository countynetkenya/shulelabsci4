<?php

namespace Modules\Learning\Controllers\Web;

use App\Controllers\BaseController;
use Modules\Learning\Services\LearningService;

class LessonsController extends BaseController
{
    protected $learningService;

    public function __construct()
    {
        $this->learningService = new LearningService();
    }

    public function create($courseId)
    {
        $course = $this->learningService->getCourse($courseId);
        if (!$course) {
            return redirect()->back()->with('error', 'Course not found.');
        }
        
        return view('Modules\Learning\Views\learning\lessons\create', ['course' => $course]);
    }

    public function store($courseId)
    {
        $data = $this->request->getPost();
        $data['course_id'] = $courseId;
        
        if ($this->learningService->addLesson($data)) {
            return redirect()->to("/learning/courses/$courseId")->with('success', 'Lesson added successfully.');
        }
        
        return redirect()->back()->withInput()->with('error', 'Failed to add lesson.');
    }
}
