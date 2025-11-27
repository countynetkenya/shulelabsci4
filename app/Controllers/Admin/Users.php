<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

/**
 * Users Controller for SuperAdmin
 * 
 * Manages user records across the entire system
 */
class Users extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * List all users
     */
    public function index()
    {
        if (!$this->isSuperAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        $db = \Config\Database::connect();
        
        $users = $db->table('users')
            ->select('users.*, roles.name as role_name, schools.name as school_name')
            ->join('user_roles', 'users.id = user_roles.user_id', 'left')
            ->join('roles', 'user_roles.role_id = roles.id', 'left')
            ->join('school_users', 'users.id = school_users.user_id', 'left')
            ->join('schools', 'school_users.school_id = schools.id', 'left')
            ->orderBy('users.username', 'ASC')
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Users',
            'users' => $users,
        ];

        return view('admin/users/index', $data);
    }

    /**
     * Show create user form
     */
    public function create()
    {
        if (!$this->isSuperAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        $data = [
            'title' => 'Add New User',
            'roles' => $this->getRoles(),
            'schools' => $this->getSchools(),
        ];

        return view('admin/users/create', $data);
    }

    /**
     * Store new user
     */
    public function store()
    {
        if (!$this->isSuperAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        $validation = \Config\Services::validation();
        
        $rules = [
            'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'first_name' => 'required|max_length[100]',
            'last_name' => 'required|max_length[100]',
            'role_id' => 'required|integer',
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
                'active' => $this->request->getPost('active') ?? 1,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $db->table('users')->insert($userData);
            $userID = $db->insertID();

            // Assign role
            $roleID = $this->request->getPost('role_id');
            $db->table('user_roles')->insert([
                'user_id' => $userID,
                'role_id' => $roleID,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // Link to school if selected
            $schoolID = $this->request->getPost('school_id');
            if ($schoolID) {
                $db->table('school_users')->insert([
                    'school_id' => $schoolID,
                    'user_id' => $userID,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->back()->withInput()->with('error', 'Failed to create user.');
            }

            return redirect()->to('/admin/users')->with('success', 'User created successfully.');

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'User creation failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'An error occurred while creating the user.');
        }
    }

    /**
     * Show edit user form
     */
    public function edit($id)
    {
        if (!$this->isSuperAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        $user = $this->userModel->find($id);
        
        if (!$user) {
            return redirect()->to('/admin/users')->with('error', 'User not found.');
        }

        $db = \Config\Database::connect();
        
        // Get user's current role
        $userRole = $db->table('user_roles')
            ->where('user_id', $id)
            ->get()
            ->getRow();

        // Get user's current school
        $userSchool = $db->table('school_users')
            ->where('user_id', $id)
            ->get()
            ->getRow();

        $data = [
            'title' => 'Edit User',
            'user' => $user,
            'roles' => $this->getRoles(),
            'schools' => $this->getSchools(),
            'currentRoleID' => $userRole->role_id ?? null,
            'currentSchoolID' => $userSchool->school_id ?? null,
        ];

        return view('admin/users/edit', $data);
    }

    /**
     * Update user
     */
    public function update($id)
    {
        if (!$this->isSuperAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        $validation = \Config\Services::validation();
        
        $rules = [
            'username' => "required|min_length[3]|max_length[50]|is_unique[users.username,id,{$id}]",
            'email' => "required|valid_email|is_unique[users.email,id,{$id}]",
            'first_name' => 'required|max_length[100]',
            'last_name' => 'required|max_length[100]',
            'role_id' => 'required|integer',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Update user
            $userData = [
                'username' => $this->request->getPost('username'),
                'email' => $this->request->getPost('email'),
                'first_name' => $this->request->getPost('first_name'),
                'last_name' => $this->request->getPost('last_name'),
                'active' => $this->request->getPost('active') ?? 1,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            // Update password if provided
            $password = $this->request->getPost('password');
            if (!empty($password)) {
                $userData['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
            }

            $db->table('users')->where('id', $id)->update($userData);

            // Update role
            $roleID = $this->request->getPost('role_id');
            $db->table('user_roles')->where('user_id', $id)->delete();
            $db->table('user_roles')->insert([
                'user_id' => $id,
                'role_id' => $roleID,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // Update school
            $schoolID = $this->request->getPost('school_id');
            $db->table('school_users')->where('user_id', $id)->delete();
            if ($schoolID) {
                $db->table('school_users')->insert([
                    'school_id' => $schoolID,
                    'user_id' => $id,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->back()->withInput()->with('error', 'Failed to update user.');
            }

            return redirect()->to('/admin/users')->with('success', 'User updated successfully.');

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'User update failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'An error occurred while updating the user.');
        }
    }

    /**
     * Delete user
     */
    public function delete($id)
    {
        if (!$this->isSuperAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        if ($this->userModel->delete($id)) {
            return redirect()->to('/admin/users')->with('success', 'User deleted successfully.');
        }

        return redirect()->to('/admin/users')->with('error', 'Failed to delete user.');
    }

    /**
     * Get all roles
     */
    private function getRoles(): array
    {
        $db = \Config\Database::connect();
        return $db->table('roles')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray() ?? [];
    }

    /**
     * Get all schools
     */
    private function getSchools(): array
    {
        $db = \Config\Database::connect();
        return $db->table('schools')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray() ?? [];
    }

    /**
     * Check if current user is superadmin
     */
    private function isSuperAdmin(): bool
    {
        $session = session();
        $userID = $session->get('userID');
        
        if (!$userID) {
            return false;
        }

        $db = \Config\Database::connect();
        $role = $db->table('users')
            ->select('roles.name')
            ->join('user_roles', 'users.id = user_roles.user_id')
            ->join('roles', 'user_roles.role_id = roles.id')
            ->where('users.id', $userID)
            ->where('roles.name', 'superadmin')
            ->get()
            ->getRow();

        return $role !== null;
    }
}
