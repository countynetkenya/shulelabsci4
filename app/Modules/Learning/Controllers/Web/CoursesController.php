<?php

namespace Modules\Learning\Controllers\Web;

use App\Controllers\BaseController;
use Modules\Learning\Services\LearningService;

class CoursesController extends BaseController
{
    protected $learningService;

    public function __construct()
    {
        helper('text');
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

    public function show($id)
    {
        $course = $this->learningService->getCourse($id);
        if (!$course) {
            return redirect()->to('/learning/courses')->with('error', 'Course not found.');
        }

        $lessons = $this->learningService->getCourseLessons($id);

        return view('Modules\Learning\Views\learning\courses\show', [
            'course' => $course,
            'lessons' => $lessons,
        ]);
    }

    public function edit($id)
    {
        $schoolId = session()->get('current_school_id') ?? session()->get('school_id') ?? 1;
        $course = $this->learningService->getCourse($id);

        if (!$course || $course['school_id'] != $schoolId) {
            return redirect()->to('/learning/courses')->with('error', 'Course not found.');
        }

        return view('Modules\Learning\Views\learning\courses\edit', ['course' => $course]);
    }

    public function update($id)
    {
        $schoolId = session()->get('current_school_id') ?? session()->get('school_id') ?? 1;
        $course = $this->learningService->getCourse($id);

        if (!$course || $course['school_id'] != $schoolId) {
            return redirect()->to('/learning/courses')->with('error', 'Course not found.');
        }

        $data = $this->request->getPost();

        if ($this->learningService->updateCourse($id, $data)) {
            return redirect()->to('/learning/courses')->with('success', 'Course updated successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update course.');
    }

    public function delete($id)
    {
        $schoolId = session()->get('current_school_id') ?? session()->get('school_id') ?? 1;
        $course = $this->learningService->getCourse($id);

        if (!$course || $course['school_id'] != $schoolId) {
            return redirect()->to('/learning/courses')->with('error', 'Course not found.');
        }

        if ($this->learningService->deleteCourse($id)) {
            return redirect()->to('/learning/courses')->with('success', 'Course deleted successfully.');
        }

        return redirect()->to('/learning/courses')->with('error', 'Failed to delete course.');
    }
}
