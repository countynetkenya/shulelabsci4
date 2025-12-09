<?php

namespace App\Modules\Student\Controllers\Web;

use App\Controllers\BaseController;
use App\Modules\Student\Services\StudentService;

/**
 * StudentController - Handles CRUD operations for students
 * 
 * All data is tenant-scoped by school_id from session.
 */
class StudentController extends BaseController
{
    protected StudentService $service;

    public function __construct()
    {
        $this->service = new StudentService();
    }

    /**
     * Check if user has permission to access student module
     */
    protected function checkAccess(): bool
    {
        // Allow admins
        $usertypeID = session()->get('usertypeID');
        $isAdmin = in_array($usertypeID, [0, 1, '0', '1']);
        
        return $isAdmin;
    }

    /**
     * Get current school ID from session
     */
    protected function getSchoolId(): int
    {
        return (int) (session()->get('school_id') ?? session()->get('schoolID') ?? 1);
    }

    /**
     * List all students
     */
    public function index()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();
        
        // Get filter parameters
        $filters = [
            'search'   => $this->request->getGet('search'),
            'status'   => $this->request->getGet('status'),
            'class_id' => $this->request->getGet('class_id'),
        ];

        $data = [
            'students' => $this->service->getAll($schoolId, array_filter($filters)),
            'filters'  => $filters,
        ];

        return view('App\Modules\Student\Views\index', $data);
    }

    /**
     * Show create form
     */
    public function create()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $data = [];

        return view('App\Modules\Student\Views\create', $data);
    }

    /**
     * Store a new student
     */
    public function store()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();
        
        // Validation rules
        $rules = [
            'first_name'       => 'required|min_length[2]|max_length[100]',
            'last_name'        => 'required|min_length[2]|max_length[100]',
            'admission_number' => 'permit_empty|max_length[50]',
            'gender'           => 'permit_empty|in_list[male,female,other]',
            'status'           => 'permit_empty|in_list[active,inactive,graduated,transferred,suspended]',
            'parent_email'     => 'permit_empty|valid_email',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'school_id'        => $schoolId,
            'student_id'       => null, // Will be set if linking to user
            'first_name'       => $this->request->getPost('first_name'),
            'last_name'        => $this->request->getPost('last_name'),
            'admission_number' => $this->request->getPost('admission_number') ?: null,
            'date_of_birth'    => $this->request->getPost('date_of_birth') ?: null,
            'gender'           => $this->request->getPost('gender') ?: null,
            'status'           => $this->request->getPost('status') ?: 'active',
            'class_id'         => $this->request->getPost('class_id') ?: null,
            'parent_name'      => $this->request->getPost('parent_name') ?: null,
            'parent_phone'     => $this->request->getPost('parent_phone') ?: null,
            'parent_email'     => $this->request->getPost('parent_email') ?: null,
        ];

        $result = $this->service->create($data);

        if ($result) {
            return redirect()->to('/students')->with('message', 'Student added successfully!');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to add student. Please try again.');
    }

    /**
     * Show edit form
     */
    public function edit(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();
        $student = $this->service->getById($id, $schoolId);
        
        if (!$student) {
            return redirect()->to('/students')->with('error', 'Student not found.');
        }

        $data = [
            'student' => $student,
        ];

        return view('App\Modules\Student\Views\edit', $data);
    }

    /**
     * Update an existing student
     */
    public function update(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();

        // Verify student exists
        $existingStudent = $this->service->getById($id, $schoolId);
        if (!$existingStudent) {
            return redirect()->to('/students')->with('error', 'Student not found.');
        }

        // Validation rules
        $rules = [
            'first_name'       => 'required|min_length[2]|max_length[100]',
            'last_name'        => 'required|min_length[2]|max_length[100]',
            'admission_number' => 'permit_empty|max_length[50]',
            'gender'           => 'permit_empty|in_list[male,female,other]',
            'status'           => 'permit_empty|in_list[active,inactive,graduated,transferred,suspended]',
            'parent_email'     => 'permit_empty|valid_email',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'first_name'       => $this->request->getPost('first_name'),
            'last_name'        => $this->request->getPost('last_name'),
            'admission_number' => $this->request->getPost('admission_number') ?: null,
            'date_of_birth'    => $this->request->getPost('date_of_birth') ?: null,
            'gender'           => $this->request->getPost('gender') ?: null,
            'status'           => $this->request->getPost('status') ?: 'active',
            'class_id'         => $this->request->getPost('class_id') ?: null,
            'parent_name'      => $this->request->getPost('parent_name') ?: null,
            'parent_phone'     => $this->request->getPost('parent_phone') ?: null,
            'parent_email'     => $this->request->getPost('parent_email') ?: null,
        ];

        $result = $this->service->update($id, $data, $schoolId);

        if ($result) {
            return redirect()->to('/students')->with('message', 'Student updated successfully!');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update student. Please try again.');
    }

    /**
     * Delete a student
     */
    public function delete(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();
        
        // Verify student exists
        $student = $this->service->getById($id, $schoolId);
        if (!$student) {
            return redirect()->to('/students')->with('error', 'Student not found.');
        }

        $result = $this->service->delete($id, $schoolId);

        if ($result) {
            return redirect()->to('/students')->with('message', 'Student deleted successfully!');
        }

        return redirect()->to('/students')->with('error', 'Failed to delete student. Please try again.');
    }
}
