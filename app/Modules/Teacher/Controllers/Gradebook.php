<?php

namespace App\Modules\Teacher\Controllers;

use App\Controllers\BaseController;

/**
 * Gradebook Controller for Teacher Module
 * 
 * Manages grade entry and viewing for teachers
 */
class Gradebook extends BaseController
{
    protected $userID;
    protected $schoolID;

    public function __construct()
    {
        $this->userID = session()->get('userID');
        $this->schoolID = session()->get('schoolID');
    }

    /**
     * Display gradebook interface
     */
    public function index()
    {
        if (!$this->userID) {
            return redirect()->to('/auth/signin')->with('error', 'Please login first.');
        }

        $data = [
            'title' => 'Gradebook',
            'classes' => $this->getMyClasses(),
            'courses' => $this->getCourses(),
        ];

        return view('modules/teacher/gradebook/index', $data);
    }

    /**
     * View grades for a specific class and course
     */
    public function view($classID = null, $courseID = null)
    {
        if (!$this->userID) {
            return redirect()->to('/auth/signin')->with('error', 'Please login first.');
        }

        if (!$classID || !$courseID) {
            return redirect()->to('/teacher/gradebook')->with('error', 'Please select a class and course.');
        }

        $db = \Config\Database::connect();
        
        // Get students in the class
        $students = $db->table('student_enrollments')
            ->select('users.id, users.username, users.first_name, users.last_name, grades.score, grades.grade, grades.id as grade_id')
            ->join('users', 'student_enrollments.student_id = users.id')
            ->join('grades', 'users.id = grades.student_id AND grades.course_id = ' . $courseID, 'left')
            ->where('student_enrollments.class_id', $classID)
            ->orderBy('users.username', 'ASC')
            ->get()
            ->getResultArray();

        $class = $db->table('school_classes')->where('id', $classID)->get()->getRow();
        $course = $db->table('courses')->where('id', $courseID)->get()->getRow();

        $data = [
            'title' => 'Gradebook - ' . ($class->name ?? '') . ' - ' . ($course->name ?? ''),
            'class' => $class,
            'course' => $course,
            'students' => $students,
            'classID' => $classID,
            'courseID' => $courseID,
        ];

        return view('modules/teacher/gradebook/view', $data);
    }

    /**
     * Save or update grade
     */
    public function save()
    {
        if (!$this->userID) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $studentID = $this->request->getPost('student_id');
        $courseID = $this->request->getPost('course_id');
        $score = $this->request->getPost('score');
        $grade = $this->request->getPost('grade');

        if (!$studentID || !$courseID) {
            return $this->response->setJSON(['success' => false, 'message' => 'Missing required fields']);
        }

        $db = \Config\Database::connect();
        
        // Check if grade exists
        $existing = $db->table('grades')
            ->where('student_id', $studentID)
            ->where('course_id', $courseID)
            ->get()
            ->getRow();

        $gradeData = [
            'student_id' => $studentID,
            'course_id' => $courseID,
            'score' => $score,
            'grade' => $grade,
            'graded_by' => $this->userID,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            // Update
            $success = $db->table('grades')->where('id', $existing->id)->update($gradeData);
        } else {
            // Insert
            $gradeData['created_at'] = date('Y-m-d H:i:s');
            $success = $db->table('grades')->insert($gradeData);
        }

        if ($success) {
            return $this->response->setJSON(['success' => true, 'message' => 'Grade saved successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to save grade']);
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

    /**
     * Get available courses
     */
    private function getCourses(): array
    {
        $db = \Config\Database::connect();
        return $db->table('courses')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray() ?? [];
    }
}
