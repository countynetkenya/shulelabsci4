<?php

namespace App\Modules\Teacher\Controllers\Web;

use App\Controllers\BaseController;
use App\Modules\Teacher\Services\TeacherService;

class TeacherController extends BaseController
{
    protected TeacherService $service;

    public function __construct()
    {
        $this->service = new TeacherService();
    }

    protected function checkAccess(): bool
    {
        $usertypeID = session()->get('usertypeID');
        return in_array($usertypeID, [0, 1, '0', '1']);
    }

    protected function getSchoolId(): int
    {
        return (int) (session()->get('school_id') ?? session()->get('schoolID') ?? 1);
    }

    public function index()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $schoolId = $this->getSchoolId();
        $filters = [
            'search'     => $this->request->getGet('search'),
            'status'     => $this->request->getGet('status'),
            'department' => $this->request->getGet('department'),
        ];

        $data = [
            'teachers'    => $this->service->getAll($schoolId, array_filter($filters)),
            'departments' => $this->service->getDepartments($schoolId),
            'filters'     => $filters,
        ];

        return view('App\Modules\Teacher\Views\index', $data);
    }

    public function create()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        return view('App\Modules\Teacher\Views\create');
    }

    public function store()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $rules = [
            'first_name'  => 'required|min_length[2]|max_length[100]',
            'last_name'   => 'required|min_length[2]|max_length[100]',
            'employee_id' => 'permit_empty|max_length[50]',
            'email'       => 'permit_empty|valid_email',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $schoolId = $this->getSchoolId();
        $data = [
            'school_id'        => $schoolId,
            'teacher_id'       => null,
            'first_name'       => $this->request->getPost('first_name'),
            'last_name'        => $this->request->getPost('last_name'),
            'employee_id'      => $this->request->getPost('employee_id') ?: null,
            'department'       => $this->request->getPost('department') ?: null,
            'subjects'         => $this->request->getPost('subjects') ?: null,
            'qualification'    => $this->request->getPost('qualification') ?: null,
            'date_of_joining'  => $this->request->getPost('date_of_joining') ?: null,
            'status'           => $this->request->getPost('status') ?: 'active',
            'phone'            => $this->request->getPost('phone') ?: null,
            'email'            => $this->request->getPost('email') ?: null,
        ];

        if ($this->service->create($data)) {
            return redirect()->to('/teachers')->with('message', 'Teacher added successfully!');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to add teacher.');
    }

    public function edit(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $schoolId = $this->getSchoolId();
        $teacher = $this->service->getById($id, $schoolId);

        if (!$teacher) {
            return redirect()->to('/teachers')->with('error', 'Teacher not found.');
        }

        return view('App\Modules\Teacher\Views\edit', ['teacher' => $teacher]);
    }

    public function update(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $schoolId = $this->getSchoolId();
        if (!$this->service->getById($id, $schoolId)) {
            return redirect()->to('/teachers')->with('error', 'Teacher not found.');
        }

        $rules = [
            'first_name'  => 'required|min_length[2]|max_length[100]',
            'last_name'   => 'required|min_length[2]|max_length[100]',
            'employee_id' => 'permit_empty|max_length[50]',
            'email'       => 'permit_empty|valid_email',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'first_name'       => $this->request->getPost('first_name'),
            'last_name'        => $this->request->getPost('last_name'),
            'employee_id'      => $this->request->getPost('employee_id') ?: null,
            'department'       => $this->request->getPost('department') ?: null,
            'subjects'         => $this->request->getPost('subjects') ?: null,
            'qualification'    => $this->request->getPost('qualification') ?: null,
            'date_of_joining'  => $this->request->getPost('date_of_joining') ?: null,
            'status'           => $this->request->getPost('status') ?: 'active',
            'phone'            => $this->request->getPost('phone') ?: null,
            'email'            => $this->request->getPost('email') ?: null,
        ];

        if ($this->service->update($id, $data, $schoolId)) {
            return redirect()->to('/teachers')->with('message', 'Teacher updated successfully!');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update teacher.');
    }

    public function delete(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $schoolId = $this->getSchoolId();
        if (!$this->service->getById($id, $schoolId)) {
            return redirect()->to('/teachers')->with('error', 'Teacher not found.');
        }

        if ($this->service->delete($id, $schoolId)) {
            return redirect()->to('/teachers')->with('message', 'Teacher deleted successfully!');
        }

        return redirect()->to('/teachers')->with('error', 'Failed to delete teacher.');
    }
}
