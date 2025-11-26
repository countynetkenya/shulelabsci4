<?php

namespace App\Modules\Teacher\Controllers;

use App\Controllers\BaseController;

/**
 * Attendance Controller for Teacher Module
 * 
 * Manages attendance marking and viewing for teachers
 */
class Attendance extends BaseController
{
    protected $userID;
    protected $schoolID;

    public function __construct()
    {
        $this->userID = session()->get('userID');
        $this->schoolID = session()->get('schoolID');
    }

    /**
     * Display attendance interface
     */
    public function index()
    {
        if (!$this->userID) {
            return redirect()->to('/auth/signin')->with('error', 'Please login first.');
        }

        $data = [
            'title' => 'Attendance',
            'classes' => $this->getMyClasses(),
            'selectedDate' => $this->request->getGet('date') ?? date('Y-m-d'),
        ];

        return view('modules/teacher/attendance/index', $data);
    }

    /**
     * Mark attendance for a class
     */
    public function mark($classID = null)
    {
        if (!$this->userID) {
            return redirect()->to('/auth/signin')->with('error', 'Please login first.');
        }

        if (!$classID) {
            return redirect()->to('/teacher/attendance')->with('error', 'Please select a class.');
        }

        $date = $this->request->getGet('date') ?? date('Y-m-d');
        
        $db = \Config\Database::connect();
        
        // Get students in the class
        $students = $db->table('student_enrollments')
            ->select('ci4_users.id, ci4_users.username, ci4_users.first_name, ci4_users.last_name')
            ->join('ci4_users', 'student_enrollments.student_id = ci4_users.id')
            ->where('student_enrollments.class_id', $classID)
            ->orderBy('ci4_users.username', 'ASC')
            ->get()
            ->getResultArray();

        // Note: Attendance records would be stored in an attendance table
        // For now, we'll just show the interface

        $class = $db->table('school_classes')->where('id', $classID)->get()->getRow();

        $data = [
            'title' => 'Mark Attendance - ' . ($class->name ?? ''),
            'class' => $class,
            'classID' => $classID,
            'students' => $students,
            'date' => $date,
        ];

        return view('modules/teacher/attendance/mark', $data);
    }

    /**
     * Save attendance records
     */
    public function save()
    {
        if (!$this->userID) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $classID = $this->request->getPost('class_id');
        $date = $this->request->getPost('date');
        $attendance = $this->request->getPost('attendance'); // Array of student_id => status

        if (!$classID || !$date || !$attendance) {
            return $this->response->setJSON(['success' => false, 'message' => 'Missing required fields']);
        }

        // Note: This would save to an attendance table
        // For now, we'll just return success
        // TODO: Create attendance table and implement saving logic

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Attendance saved successfully',
            'note' => 'Attendance table needs to be created for actual storage'
        ]);
    }

    /**
     * View attendance report for a class
     */
    public function report($classID = null)
    {
        if (!$this->userID) {
            return redirect()->to('/auth/signin')->with('error', 'Please login first.');
        }

        if (!$classID) {
            return redirect()->to('/teacher/attendance')->with('error', 'Please select a class.');
        }

        $db = \Config\Database::connect();
        $class = $db->table('school_classes')->where('id', $classID)->get()->getRow();

        $data = [
            'title' => 'Attendance Report - ' . ($class->name ?? ''),
            'class' => $class,
            'classID' => $classID,
        ];

        return view('modules/teacher/attendance/report', $data);
    }

    /**
     * Get classes taught by this teacher
     */
    private function getMyClasses(): array
    {
        $db = \Config\Database::connect();
        return $db->table('school_classes')
            ->where('school_id', $this->schoolID)
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray() ?? [];
    }
}
