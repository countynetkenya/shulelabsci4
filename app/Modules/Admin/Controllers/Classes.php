<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;

/**
 * Classes Controller for Admin Module
 * 
 * Manages class records within a school
 */
class Classes extends BaseController
{
    protected $schoolID;

    public function __construct()
    {
        $this->schoolID = session()->get('schoolID');
    }

    /**
     * List all classes in the school
     */
    public function index()
    {
        if (!$this->schoolID) {
            return redirect()->to('/school/select')->with('error', 'Please select a school first.');
        }

        $db = \Config\Database::connect();
        
        $classes = $db->table('school_classes')
            ->select('school_classes.*, COUNT(DISTINCT student_enrollments.student_id) as student_count')
            ->join('student_enrollments', 'school_classes.id = student_enrollments.class_id', 'left')
            ->where('school_classes.school_id', $this->schoolID)
            ->groupBy('school_classes.id')
            ->orderBy('school_classes.name', 'ASC')
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Classes',
            'classes' => $classes,
        ];

        return view('modules/admin/classes/index', $data);
    }

    /**
     * Show create class form
     */
    public function create()
    {
        if (!$this->schoolID) {
            return redirect()->to('/school/select')->with('error', 'Please select a school first.');
        }

        $data = [
            'title' => 'Add New Class',
            'teachers' => $this->getSchoolTeachers(),
        ];

        return view('modules/admin/classes/create', $data);
    }

    /**
     * Store new class
     */
    public function store()
    {
        if (!$this->schoolID) {
            return redirect()->to('/school/select')->with('error', 'Please select a school first.');
        }

        $validation = \Config\Services::validation();
        
        $rules = [
            'name' => 'required|max_length[100]',
            'grade_level' => 'permit_empty|max_length[50]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $db = \Config\Database::connect();

        $classData = [
            'school_id' => $this->schoolID,
            'name' => $this->request->getPost('name'),
            'grade_level' => $this->request->getPost('grade_level'),
            'academic_year' => $this->request->getPost('academic_year') ?? date('Y'),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($db->table('school_classes')->insert($classData)) {
            return redirect()->to('/admin/classes')->with('success', 'Class created successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create class.');
    }

    /**
     * Show edit class form
     */
    public function edit($id)
    {
        if (!$this->schoolID) {
            return redirect()->to('/school/select')->with('error', 'Please select a school first.');
        }

        $db = \Config\Database::connect();
        $class = $db->table('school_classes')
            ->where('id', $id)
            ->where('school_id', $this->schoolID)
            ->get()
            ->getRow();
        
        if (!$class) {
            return redirect()->to('/admin/classes')->with('error', 'Class not found.');
        }

        $data = [
            'title' => 'Edit Class',
            'class' => $class,
            'teachers' => $this->getSchoolTeachers(),
        ];

        return view('modules/admin/classes/edit', $data);
    }

    /**
     * Update class
     */
    public function update($id)
    {
        if (!$this->schoolID) {
            return redirect()->to('/school/select')->with('error', 'Please select a school first.');
        }

        $validation = \Config\Services::validation();
        
        $rules = [
            'name' => 'required|max_length[100]',
            'grade_level' => 'permit_empty|max_length[50]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $db = \Config\Database::connect();

        $classData = [
            'name' => $this->request->getPost('name'),
            'grade_level' => $this->request->getPost('grade_level'),
            'academic_year' => $this->request->getPost('academic_year'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($db->table('school_classes')->where('id', $id)->where('school_id', $this->schoolID)->update($classData)) {
            return redirect()->to('/admin/classes')->with('success', 'Class updated successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update class.');
    }

    /**
     * Delete class
     */
    public function delete($id)
    {
        if (!$this->schoolID) {
            return redirect()->to('/school/select')->with('error', 'Please select a school first.');
        }

        $db = \Config\Database::connect();
        
        if ($db->table('school_classes')->where('id', $id)->where('school_id', $this->schoolID)->delete()) {
            return redirect()->to('/admin/classes')->with('success', 'Class deleted successfully.');
        }

        return redirect()->to('/admin/classes')->with('error', 'Failed to delete class.');
    }

    /**
     * Get teachers for the current school
     */
    private function getSchoolTeachers(): array
    {
        $db = \Config\Database::connect();
        return $db->table('ci4_users')
            ->select('ci4_users.id, ci4_users.username, ci4_users.first_name, ci4_users.last_name')
            ->join('ci4_user_roles', 'ci4_users.id = ci4_user_roles.user_id')
            ->join('ci4_roles', 'ci4_user_roles.role_id = ci4_roles.id')
            ->join('school_users', 'ci4_users.id = school_users.user_id')
            ->where('ci4_roles.name', 'teacher')
            ->where('school_users.school_id', $this->schoolID)
            ->orderBy('ci4_users.username', 'ASC')
            ->get()
            ->getResultArray() ?? [];
    }
}
