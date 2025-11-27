<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

/**
 * Teachers Controller for Admin Module.
 *
 * Manages teacher records within a school
 */
class Teachers extends BaseController
{
    protected $userModel;

    protected $schoolID;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->schoolID = session()->get('schoolID');
    }

    /**
     * List all teachers in the school.
     */
    public function index()
    {
        if (!$this->schoolID) {
            return redirect()->to('/school/select')->with('error', 'Please select a school first.');
        }

        $db = \Config\Database::connect();

        $teachers = $db->table('users')
            ->select('users.*, school_users.school_id, schools.name as school_name')
            ->join('user_roles', 'users.id = user_roles.user_id')
            ->join('roles', 'user_roles.role_id = roles.id')
            ->join('school_users', 'users.id = school_users.user_id', 'left')
            ->join('schools', 'school_users.school_id = schools.id', 'left')
            ->where('roles.name', 'teacher')
            ->where('school_users.school_id', $this->schoolID)
            ->orderBy('users.username', 'ASC')
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Teachers',
            'teachers' => $teachers,
        ];

        return view('modules/admin/teachers/index', $data);
    }

    /**
     * Show create teacher form.
     */
    public function create()
    {
        if (!$this->schoolID) {
            return redirect()->to('/school/select')->with('error', 'Please select a school first.');
        }

        $data = [
            'title' => 'Add New Teacher',
            'subjects' => $this->getSubjects(),
        ];

        return view('modules/admin/teachers/create', $data);
    }

    /**
     * Store new teacher.
     */
    public function store()
    {
        if (!$this->schoolID) {
            return redirect()->to('/school/select')->with('error', 'Please select a school first.');
        }

        $validation = \Config\Services::validation();

        $rules = [
            'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'first_name' => 'required|max_length[100]',
            'last_name' => 'required|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Create user
            $userData = [
                'username' => $this->request->getPost('username'),
                'email' => $this->request->getPost('email'),
                'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
                'first_name' => $this->request->getPost('first_name'),
                'last_name' => $this->request->getPost('last_name'),
                'active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $db->table('users')->insert($userData);
            $userID = $db->insertID();

            // Get teacher role
            $teacherRole = $db->table('roles')->where('name', 'teacher')->get()->getRow();

            if ($teacherRole) {
                // Assign teacher role
                $db->table('user_roles')->insert([
                    'user_id' => $userID,
                    'role_id' => $teacherRole->id,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            // Link to school
            $db->table('school_users')->insert([
                'school_id' => $this->schoolID,
                'user_id' => $userID,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->back()->withInput()->with('error', 'Failed to create teacher.');
            }

            return redirect()->to('/admin/teachers')->with('success', 'Teacher created successfully.');

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Teacher creation failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'An error occurred while creating the teacher.');
        }
    }

    /**
     * Show edit teacher form.
     */
    public function edit($id)
    {
        if (!$this->schoolID) {
            return redirect()->to('/school/select')->with('error', 'Please select a school first.');
        }

        $teacher = $this->userModel->find($id);

        if (!$teacher) {
            return redirect()->to('/admin/teachers')->with('error', 'Teacher not found.');
        }

        $data = [
            'title' => 'Edit Teacher',
            'teacher' => $teacher,
            'subjects' => $this->getSubjects(),
        ];

        return view('modules/admin/teachers/edit', $data);
    }

    /**
     * Update teacher.
     */
    public function update($id)
    {
        if (!$this->schoolID) {
            return redirect()->to('/school/select')->with('error', 'Please select a school first.');
        }

        $validation = \Config\Services::validation();

        $rules = [
            'username' => "required|min_length[3]|max_length[50]|is_unique[users.username,id,{$id}]",
            'email' => "required|valid_email|is_unique[users.email,id,{$id}]",
            'first_name' => 'required|max_length[100]',
            'last_name' => 'required|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $userData = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Update password if provided
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $userData['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
        }

        if ($this->userModel->update($id, $userData)) {
            return redirect()->to('/admin/teachers')->with('success', 'Teacher updated successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update teacher.');
    }

    /**
     * Delete teacher.
     */
    public function delete($id)
    {
        if (!$this->schoolID) {
            return redirect()->to('/school/select')->with('error', 'Please select a school first.');
        }

        if ($this->userModel->delete($id)) {
            return redirect()->to('/admin/teachers')->with('success', 'Teacher deleted successfully.');
        }

        return redirect()->to('/admin/teachers')->with('error', 'Failed to delete teacher.');
    }

    /**
     * Get available subjects.
     */
    private function getSubjects(): array
    {
        $db = \Config\Database::connect();
        return $db->table('courses')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray() ?? [];
    }
}
