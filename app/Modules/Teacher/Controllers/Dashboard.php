<?php

namespace App\Modules\Teacher\Controllers;

use App\Controllers\BaseController;

/**
 * Teacher Dashboard Controller.
 *
 * Dashboard for teacher users showing their classes and assignments
 */
class Dashboard extends BaseController
{
    protected $userID;

    protected $schoolID;

    public function __construct()
    {
        $this->userID = session()->get('userID');
        $this->schoolID = session()->get('schoolID');
    }

    /**
     * Display teacher dashboard.
     */
    public function index()
    {
        if (!$this->userID) {
            return redirect()->to('/auth/signin')->with('error', 'Please login first.');
        }

        $data = [
            'title' => 'Teacher Dashboard',
            'teacherInfo' => $this->getTeacherInfo(),
            'myClasses' => $this->getMyClasses(),
            'totalStudents' => $this->getTotalStudents(),
            'upcomingAssignments' => $this->getUpcomingAssignments(),
            'recentGrades' => $this->getRecentGrades(),
            'todaySchedule' => $this->getTodaySchedule(),
        ];

        return view('modules/teacher/dashboard/index', $data);
    }

    /**
     * Get teacher information.
     */
    private function getTeacherInfo(): ?object
    {
        $db = \Config\Database::connect();
        return $db->table('users')
            ->select('users.*, schools.name as school_name')
            ->join('school_users', 'users.id = school_users.user_id', 'left')
            ->join('schools', 'school_users.school_id = schools.id', 'left')
            ->where('users.id', $this->userID)
            ->get()
            ->getRow();
    }

    /**
     * Get classes assigned to this teacher.
     */
    private function getMyClasses(): array
    {
        $db = \Config\Database::connect();

        // Get classes where this teacher is the class teacher or teaches a subject
        return $db->table('school_classes')
            ->select('school_classes.*, COUNT(DISTINCT student_enrollments.student_id) as student_count')
            ->join('student_enrollments', 'school_classes.id = student_enrollments.class_id', 'left')
            ->where('school_classes.school_id', $this->schoolID)
            ->groupBy('school_classes.id')
            ->get()
            ->getResultArray() ?? [];
    }

    /**
     * Get total students taught by this teacher.
     */
    private function getTotalStudents(): int
    {
        $db = \Config\Database::connect();

        return (int) $db->table('student_enrollments')
            ->join('school_classes', 'student_enrollments.class_id = school_classes.id')
            ->where('school_classes.school_id', $this->schoolID)
            ->countAllResults();
    }

    /**
     * Get upcoming assignments.
     */
    private function getUpcomingAssignments(): array
    {
        $db = \Config\Database::connect();

        return $db->table('assignments')
            ->select('assignments.*, school_classes.name as class_name')
            ->join('school_classes', 'assignments.class_id = school_classes.id', 'left')
            ->where('assignments.due_date >=', date('Y-m-d'))
            ->orderBy('assignments.due_date', 'ASC')
            ->limit(5)
            ->get()
            ->getResultArray() ?? [];
    }

    /**
     * Get recent grades submitted.
     */
    private function getRecentGrades(): array
    {
        $db = \Config\Database::connect();

        return $db->table('grades')
            ->select('grades.*, users.username as student_name, courses.name as course_name')
            ->join('users', 'grades.student_id = users.id', 'left')
            ->join('courses', 'grades.course_id = courses.id', 'left')
            ->orderBy('grades.created_at', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray() ?? [];
    }

    /**
     * Get today's class schedule.
     */
    private function getTodaySchedule(): array
    {
        // Placeholder - implement when timetable module is ready
        return [];
    }
}
