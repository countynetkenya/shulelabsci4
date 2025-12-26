<?php

namespace Modules\LMS\Controllers;

use App\Controllers\BaseController;
use Modules\LMS\Models\CourseModel;
use Modules\LMS\Models\LessonModel;

class CoursesWebController extends BaseController
{
    protected $courseModel;

    protected $lessonModel;

    public function __construct()
    {
        $this->courseModel = new CourseModel();
        $this->lessonModel = new LessonModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Courses',
            'courses' => $this->courseModel->findAll(),
        ];

        return view('Modules\LMS\Views\Courses\index', $data);
    }

    public function new()
    {
        $data = [
            'title' => 'Create Course',
        ];

        return view('Modules\LMS\Views\Courses\form', $data);
    }

    public function create()
    {
        $rules = [
            'title' => 'required|max_length[255]',
            'status' => 'in_list[draft,published,archived]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'school_id' => session('school_id'),
            'teacher_id' => session('user_id'),
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'status' => $this->request->getPost('status'),
        ];

        if ($this->courseModel->insert($data)) {
            return redirect()->to('lms/courses')->with('message', 'Course created successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create course.');
    }

    public function show($id)
    {
        $course = $this->courseModel->find($id);

        if (!$course) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => $course['title'],
            'course' => $course,
            'lessons' => $this->lessonModel->where('course_id', $id)->orderBy('sequence_order', 'ASC')->findAll(),
        ];

        return view('Modules\LMS\Views\Courses\show', $data);
    }

    public function edit($id)
    {
        $course = $this->courseModel->find($id);

        if (!$course) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => 'Edit Course',
            'course' => $course,
        ];

        return view('Modules\LMS\Views\Courses\form', $data);
    }

    public function update($id)
    {
        $course = $this->courseModel->find($id);

        if (!$course) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $rules = [
            'title' => 'required|max_length[255]',
            'status' => 'in_list[draft,published,archived]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'status' => $this->request->getPost('status'),
        ];

        if ($this->courseModel->update($id, $data)) {
            return redirect()->to("lms/courses/{$id}")->with('message', 'Course updated successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update course.');
    }
}
