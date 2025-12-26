<?php

namespace App\Modules\Admissions\Controllers\Web;

use App\Controllers\BaseController;
use App\Modules\Admissions\Services\AdmissionsCrudService;

/**
 * AdmissionsController - Handles CRUD operations for student applications.
 *
 * All data is tenant-scoped by school_id from session.
 */
class AdmissionsController extends BaseController
{
    protected AdmissionsCrudService $service;

    public function __construct()
    {
        $this->service = new AdmissionsCrudService();
    }

    /**
     * Get current school ID from session.
     */
    protected function getSchoolId(): int
    {
        return (int) (session()->get('school_id') ?? session()->get('schoolID') ?? 1);
    }

    /**
     * Get current user ID from session.
     */
    protected function getUserId(): int
    {
        return (int) (session()->get('user_id') ?? session()->get('loginuserID') ?? 1);
    }

    /**
     * List all applications.
     */
    public function index()
    {
        $schoolId = $this->getSchoolId();

        $data = [
            'applications' => $this->service->getAll($schoolId),
            'statistics' => $this->service->getStatistics($schoolId),
        ];

        return view('App\Modules\Admissions\Views\index', $data);
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $data = [
            'currentYear' => date('Y'),
        ];

        return view('App\Modules\Admissions\Views\create', $data);
    }

    /**
     * Store new application.
     */
    public function store()
    {
        $schoolId = $this->getSchoolId();

        $validationRules = [
            'student_first_name' => 'required|min_length[2]|max_length[100]',
            'student_last_name' => 'required|min_length[2]|max_length[100]',
            'student_dob' => 'required|valid_date',
            'student_gender' => 'required|in_list[male,female,other]',
            'class_applied' => 'required|integer',
            'parent_first_name' => 'required|min_length[2]|max_length[100]',
            'parent_last_name' => 'required|min_length[2]|max_length[100]',
            'parent_email' => 'required|valid_email',
            'parent_phone' => 'required|min_length[10]',
            'parent_relationship' => 'required|in_list[father,mother,guardian]',
            'academic_year' => 'required',
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'school_id' => $schoolId,
            'academic_year' => $this->request->getPost('academic_year'),
            'term' => $this->request->getPost('term'),
            'class_applied' => $this->request->getPost('class_applied'),
            'student_first_name' => $this->request->getPost('student_first_name'),
            'student_last_name' => $this->request->getPost('student_last_name'),
            'student_dob' => $this->request->getPost('student_dob'),
            'student_gender' => $this->request->getPost('student_gender'),
            'previous_school' => $this->request->getPost('previous_school'),
            'parent_first_name' => $this->request->getPost('parent_first_name'),
            'parent_last_name' => $this->request->getPost('parent_last_name'),
            'parent_email' => $this->request->getPost('parent_email'),
            'parent_phone' => $this->request->getPost('parent_phone'),
            'parent_relationship' => $this->request->getPost('parent_relationship'),
            'address' => $this->request->getPost('address'),
            'status' => 'submitted',
        ];

        $id = $this->service->create($data);

        if ($id) {
            return redirect()->to('/admissions')->with('message', 'Application submitted successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create application');
    }

    /**
     * Show edit form.
     */
    public function edit($id)
    {
        $schoolId = $this->getSchoolId();
        $application = $this->service->getById($id, $schoolId);

        if (!$application) {
            return redirect()->to('/admissions')->with('error', 'Application not found');
        }

        $data = [
            'application' => $application,
        ];

        return view('App\Modules\Admissions\Views\edit', $data);
    }

    /**
     * Update existing application.
     */
    public function update($id)
    {
        $schoolId = $this->getSchoolId();

        $validationRules = [
            'student_first_name' => 'required|min_length[2]|max_length[100]',
            'student_last_name' => 'required|min_length[2]|max_length[100]',
            'class_applied' => 'required|integer',
            'parent_email' => 'required|valid_email',
            'parent_phone' => 'required|min_length[10]',
            'status' => 'permit_empty|in_list[submitted,under_review,interview_scheduled,test_scheduled,accepted,rejected,waitlisted,enrolled]',
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'student_first_name' => $this->request->getPost('student_first_name'),
            'student_last_name' => $this->request->getPost('student_last_name'),
            'class_applied' => $this->request->getPost('class_applied'),
            'parent_first_name' => $this->request->getPost('parent_first_name'),
            'parent_last_name' => $this->request->getPost('parent_last_name'),
            'parent_email' => $this->request->getPost('parent_email'),
            'parent_phone' => $this->request->getPost('parent_phone'),
            'parent_relationship' => $this->request->getPost('parent_relationship'),
            'address' => $this->request->getPost('address'),
            'status' => $this->request->getPost('status'),
            'decision_notes' => $this->request->getPost('decision_notes'),
        ];

        $success = $this->service->update($id, $data, $schoolId);

        if ($success) {
            return redirect()->to('/admissions')->with('message', 'Application updated successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update application');
    }

    /**
     * Delete application.
     */
    public function delete($id)
    {
        $schoolId = $this->getSchoolId();
        $success = $this->service->delete($id, $schoolId);

        if ($success) {
            return redirect()->to('/admissions')->with('message', 'Application deleted successfully');
        }

        return redirect()->to('/admissions')->with('error', 'Failed to delete application');
    }
}
