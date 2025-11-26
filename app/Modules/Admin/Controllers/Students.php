<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

/**
 * Students Controller for Admin Module
 * 
 * Manages student records within a school
 */
class Students extends BaseController
{
    protected $userModel;
    protected $schoolID;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->schoolID = session()->get('schoolID');
    }

    /**
     * List all students in the school
     */
    public function index()
    {
        if (!$this->schoolID) {
            return redirect()->to('/school/select')->with('error', 'Please select a school first.');
        }

        $db = \Config\Database::connect();
        
        $students = $db->table('ci4_users')
            ->select('ci4_users.*, school_users.school_id, schools.name as school_name')
            ->join('ci4_user_roles', 'ci4_users.id = ci4_user_roles.user_id')
            ->join('ci4_roles', 'ci4_user_roles.role_id = ci4_roles.id')
            ->join('school_users', 'ci4_users.id = school_users.user_id', 'left')
            ->join('schools', 'school_users.school_id = schools.id', 'left')
            ->where('ci4_roles.name', 'student')
            ->where('school_users.school_id', $this->schoolID)
            ->orderBy('ci4_users.username', 'ASC')
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Students',
            'students' => $students,
        ];

        return view('modules/admin/students/index', $data);
    }

    /**
     * Show create student form
     */
    public function create()
    {
        if (!$this->schoolID) {
            return redirect()->to('/school/select')->with('error', 'Please select a school first.');
        }

        $data = [
            'title' => 'Add New Student',
            'classes' => $this->getSchoolClasses(),
        ];

        return view('modules/admin/students/create', $data);
    }

    /**
     * Store new student
     */
    public function store()
    {
        if (!$this->schoolID) {
            return redirect()->to('/school/select')->with('error', 'Please select a school first.');
        }

        $validation = \Config\Services::validation();
        
        $rules = [
            'username' => 'required|min_length[3]|max_length[50]|is_unique[ci4_users.username]',
            'email' => 'required|valid_email|is_unique[ci4_users.email]',
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

            $db->table('ci4_users')->insert($userData);
            $userID = $db->insertID();

            // Get student role
            $studentRole = $db->table('ci4_roles')->where('name', 'student')->get()->getRow();
            
            if ($studentRole) {
                // Assign student role
                $db->table('ci4_user_roles')->insert([
                    'user_id' => $userID,
                    'role_id' => $studentRole->id,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            // Link to school
            $db->table('school_users')->insert([
                'school_id' => $this->schoolID,
                'user_id' => $userID,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // Enroll in class if selected
            $classID = $this->request->getPost('class_id');
            if ($classID) {
                $db->table('student_enrollments')->insert([
                    'student_id' => $userID,
                    'class_id' => $classID,
                    'enrollment_date' => date('Y-m-d'),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->back()->withInput()->with('error', 'Failed to create student.');
            }

            return redirect()->to('/admin/students')->with('success', 'Student created successfully.');

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Student creation failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'An error occurred while creating the student.');
        }
    }

    /**
     * Show edit student form
     */
    public function edit($id)
    {
        if (!$this->schoolID) {
            return redirect()->to('/school/select')->with('error', 'Please select a school first.');
        }

        $student = $this->userModel->find($id);
        
        if (!$student) {
            return redirect()->to('/admin/students')->with('error', 'Student not found.');
        }

        $data = [
            'title' => 'Edit Student',
            'student' => $student,
            'classes' => $this->getSchoolClasses(),
        ];

        return view('modules/admin/students/edit', $data);
    }

    /**
     * Update student
     */
    public function update($id)
    {
        if (!$this->schoolID) {
            return redirect()->to('/school/select')->with('error', 'Please select a school first.');
        }

        $validation = \Config\Services::validation();
        
        $rules = [
            'username' => "required|min_length[3]|max_length[50]|is_unique[ci4_users.username,id,{$id}]",
            'email' => "required|valid_email|is_unique[ci4_users.email,id,{$id}]",
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
            return redirect()->to('/admin/students')->with('success', 'Student updated successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update student.');
    }

    /**
     * Delete student
     */
    public function delete($id)
    {
        if (!$this->schoolID) {
            return redirect()->to('/school/select')->with('error', 'Please select a school first.');
        }

        if ($this->userModel->delete($id)) {
            return redirect()->to('/admin/students')->with('success', 'Student deleted successfully.');
        }

        return redirect()->to('/admin/students')->with('error', 'Failed to delete student.');
    }

    /**
     * Get classes for the current school
     */
    private function getSchoolClasses(): array
    {
        $db = \Config\Database::connect();
        return $db->table('school_classes')
            ->where('school_id', $this->schoolID)
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray() ?? [];
    }
}
