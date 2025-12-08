<?php

namespace Modules\Learning\Controllers\Web;

use App\Controllers\BaseController;
use Modules\Learning\Services\LearningService;

class CoursesController extends BaseController
{
    protected $learningService;

    public function __construct()
    {
        $this->learningService = new LearningService();
    }

    public function index()
    {
        $schoolId = session()->get('current_school_id') ?? 1;
        $courses = $this->learningService->getSchoolCourses($schoolId);
        
        return view('Modules\Learning\Views\learning\courses\index', ['courses' => $courses]);
    }

    public function create()
    {
        return view('Modules\Learning\Views\learning\courses\create');
    }

    public function store()
    {
        $data = $this->request->getPost();
        $data['school_id'] = session()->get('current_school_id') ?? 1;
        $data['teacher_id'] = session()->get('user_id') ?? 1;
        
        if ($this->learningService->createCourse($data)) {
            return redirect()->to('/learning/courses')->with('success', 'Course created successfully.');
        }
        
        return redirect()->back()->withInput()->with('error', 'Failed to create course.');
    }
}
