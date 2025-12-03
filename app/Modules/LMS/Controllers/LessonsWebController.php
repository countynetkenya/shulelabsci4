<?php

namespace Modules\LMS\Controllers;

use App\Controllers\BaseController;
use Modules\LMS\Models\CourseModel;
use Modules\LMS\Models\LessonModel;

class LessonsWebController extends BaseController
{
    protected $courseModel;
    protected $lessonModel;

    public function __construct()
    {
        $this->courseModel = new CourseModel();
        $this->lessonModel = new LessonModel();
    }

    public function new($courseId)
    {
        $course = $this->courseModel->find($courseId);

        if (!$course) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => 'Add Lesson',
            'course' => $course,
        ];

        return view('Modules\LMS\Views\Lessons\form', $data);
    }

    public function create($courseId)
    {
        $course = $this->courseModel->find($courseId);

        if (!$course) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $rules = [
            'title' => 'required|max_length[255]',
            'sequence_order' => 'required|integer',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'course_id' => $courseId,
            'title' => $this->request->getPost('title'),
            'content' => $this->request->getPost('content'),
            'video_url' => $this->request->getPost('video_url'),
            'sequence_order' => $this->request->getPost('sequence_order'),
        ];

        if ($this->lessonModel->insert($data)) {
            return redirect()->to("lms/courses/{$courseId}")->with('message', 'Lesson added successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to add lesson.');
    }

    public function edit($id)
    {
        $lesson = $this->lessonModel->find($id);

        if (!$lesson) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $course = $this->courseModel->scopeToTenant()->find($lesson['course_id']);
        if (!$course) {
             throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => 'Edit Lesson',
            'lesson' => $lesson,
            'course' => $course,
        ];

        return view('Modules\LMS\Views\Lessons\form', $data);
    }

    public function update($id)
    {
        $lesson = $this->lessonModel->find($id);

        if (!$lesson) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $course = $this->courseModel->scopeToTenant()->find($lesson['course_id']);
        if (!$course) {
             throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $rules = [
            'title' => 'required|max_length[255]',
            'sequence_order' => 'required|integer',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'title' => $this->request->getPost('title'),
            'content' => $this->request->getPost('content'),
            'video_url' => $this->request->getPost('video_url'),
            'sequence_order' => $this->request->getPost('sequence_order'),
        ];

        if ($this->lessonModel->update($id, $data)) {
            return redirect()->to("lms/courses/{$course['id']}")->with('message', 'Lesson updated successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update lesson.');
    }
}
