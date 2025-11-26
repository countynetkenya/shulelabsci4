<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * Schools Controller for SuperAdmin
 * 
 * Manages school records across the entire system
 */
class Schools extends BaseController
{
    /**
     * List all schools
     */
    public function index()
    {
        if (!$this->isSuperAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        $db = \Config\Database::connect();
        
        $schools = $db->table('schools')
            ->select('schools.*, 
                     (SELECT COUNT(*) FROM school_users WHERE school_users.school_id = schools.id) as user_count')
            ->orderBy('schools.name', 'ASC')
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Schools',
            'schools' => $schools,
        ];

        return view('admin/schools/index', $data);
    }

    /**
     * Show create school form
     */
    public function create()
    {
        if (!$this->isSuperAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        $data = [
            'title' => 'Add New School',
        ];

        return view('admin/schools/create', $data);
    }

    /**
     * Store new school
     */
    public function store()
    {
        if (!$this->isSuperAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        $validation = \Config\Services::validation();
        
        $rules = [
            'name' => 'required|max_length[255]',
            'code' => 'required|max_length[50]|is_unique[schools.code]',
            'email' => 'permit_empty|valid_email',
            'phone' => 'permit_empty|max_length[50]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $db = \Config\Database::connect();

        $schoolData = [
            'name' => $this->request->getPost('name'),
            'code' => $this->request->getPost('code'),
            'address' => $this->request->getPost('address'),
            'city' => $this->request->getPost('city'),
            'country' => $this->request->getPost('country') ?? 'Kenya',
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'subscription_tier' => $this->request->getPost('subscription_tier') ?? 'basic',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($db->table('schools')->insert($schoolData)) {
            return redirect()->to('/admin/schools')->with('success', 'School created successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create school.');
    }

    /**
     * Show edit school form
     */
    public function edit($id)
    {
        if (!$this->isSuperAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        $db = \Config\Database::connect();
        $school = $db->table('schools')->where('id', $id)->get()->getRow();
        
        if (!$school) {
            return redirect()->to('/admin/schools')->with('error', 'School not found.');
        }

        $data = [
            'title' => 'Edit School',
            'school' => $school,
        ];

        return view('admin/schools/edit', $data);
    }

    /**
     * Update school
     */
    public function update($id)
    {
        if (!$this->isSuperAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        $validation = \Config\Services::validation();
        
        $rules = [
            'name' => 'required|max_length[255]',
            'code' => "required|max_length[50]|is_unique[schools.code,id,{$id}]",
            'email' => 'permit_empty|valid_email',
            'phone' => 'permit_empty|max_length[50]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $db = \Config\Database::connect();

        $schoolData = [
            'name' => $this->request->getPost('name'),
            'code' => $this->request->getPost('code'),
            'address' => $this->request->getPost('address'),
            'city' => $this->request->getPost('city'),
            'country' => $this->request->getPost('country'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'subscription_tier' => $this->request->getPost('subscription_tier'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($db->table('schools')->where('id', $id)->update($schoolData)) {
            return redirect()->to('/admin/schools')->with('success', 'School updated successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update school.');
    }

    /**
     * Delete school
     */
    public function delete($id)
    {
        if (!$this->isSuperAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        $db = \Config\Database::connect();
        
        // Check if school has users
        $userCount = $db->table('school_users')->where('school_id', $id)->countAllResults();
        
        if ($userCount > 0) {
            return redirect()->to('/admin/schools')->with('error', 'Cannot delete school with existing users. Please remove all users first.');
        }

        if ($db->table('schools')->where('id', $id)->delete()) {
            return redirect()->to('/admin/schools')->with('success', 'School deleted successfully.');
        }

        return redirect()->to('/admin/schools')->with('error', 'Failed to delete school.');
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
        $role = $db->table('ci4_users')
            ->select('ci4_roles.name')
            ->join('ci4_user_roles', 'ci4_users.id = ci4_user_roles.user_id')
            ->join('ci4_roles', 'ci4_user_roles.role_id = ci4_roles.id')
            ->where('ci4_users.id', $userID)
            ->where('ci4_roles.name', 'superadmin')
            ->get()
            ->getRow();

        return $role !== null;
    }
}
