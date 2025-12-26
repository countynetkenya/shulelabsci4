<?php

namespace Modules\LMS\Controllers\Web;

use App\Controllers\BaseController;
use Modules\LMS\Services\LMSCourseService;

/**
 * LMSCourseController - Web CRUD for LMS course management
 */
class LMSCourseController extends BaseController
{
    protected LMSCourseService $service;

    public function __construct()
    {
        $this->service = new LMSCourseService();
    }

    /**
     * Display all courses
     */
    public function index()
    {
        if (!session()->get('is_logged_in')) {
            return redirect()->to('/login');
        }

        $schoolId = session()->get('school_id') ?? 1;
        
        $data = [
            'title' => 'LMS Courses',
            'courses' => $this->service->getAll($schoolId),
            'statistics' => $this->service->getStatistics($schoolId),
        ];

        return view('Modules\LMS\Views\courses\index', $data);
    }

    /**
     * Show create form
     */
    public function create()
    {
        if (!session()->get('is_logged_in')) {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Create LMS Course',
        ];

        return view('Modules\LMS\Views\courses\create', $data);
    }

    /**
     * Store a new course
     */
    public function store()
    {
        if (!session()->get('is_logged_in')) {
            return redirect()->to('/login');
        }

        $schoolId = session()->get('school_id') ?? 1;
        $userId = session()->get('user_id') ?? 1;

        $rules = [
            'title' => 'required|max_length[255]',
            'description' => 'permit_empty',
            'status' => 'required|in_list[draft,published,archived]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'school_id' => $schoolId,
            'teacher_id' => $userId,
            'instructor_id' => $userId,
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'modules' => $this->request->getPost('modules'),
            'status' => $this->request->getPost('status'),
        ];

        $id = $this->service->create($data);

        if ($id) {
            return redirect()->to('/lms/courses')->with('success', 'Course created successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create course');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        if (!session()->get('is_logged_in')) {
            return redirect()->to('/login');
        }

        $schoolId = session()->get('school_id') ?? 1;
        $course = $this->service->getById($id, $schoolId);

        if (!$course) {
            return redirect()->to('/lms/courses')->with('error', 'Course not found');
        }

        $data = [
            'title' => 'Edit LMS Course',
            'course' => $course,
        ];

        return view('Modules\LMS\Views\courses\edit', $data);
    }

    /**
     * Update a course
     */
    public function update($id)
    {
        if (!session()->get('is_logged_in')) {
            return redirect()->to('/login');
        }

        $rules = [
            'title' => 'required|max_length[255]',
            'description' => 'permit_empty',
            'status' => 'required|in_list[draft,published,archived]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'modules' => $this->request->getPost('modules'),
            'status' => $this->request->getPost('status'),
        ];

        if ($this->service->update($id, $data)) {
            return redirect()->to('/lms/courses')->with('success', 'Course updated successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update course');
    }

    /**
     * Delete a course
     */
    public function delete($id)
    {
        if (!session()->get('is_logged_in')) {
            return redirect()->to('/login');
        }

        if ($this->service->delete($id)) {
            return redirect()->to('/lms/courses')->with('success', 'Course deleted successfully');
        }

        return redirect()->to('/lms/courses')->with('error', 'Failed to delete course');
    }
}
