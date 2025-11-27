<?php

namespace App\Modules\Student\Controllers;

use App\Controllers\BaseController;

/**
 * Student Dashboard Controller.
 *
 * Dashboard for student users showing their classes, assignments, and grades
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
     * Display student dashboard.
     */
    public function index()
    {
        if (!$this->userID) {
            return redirect()->to('/auth/signin')->with('error', 'Please login first.');
        }

        $data = [
            'title' => 'Student Dashboard',
            'studentInfo' => $this->getStudentInfo(),
            'myClasses' => $this->getMyClasses(),
            'pendingAssignments' => $this->getPendingAssignments(),
            'recentGrades' => $this->getRecentGrades(),
            'attendanceStats' => $this->getAttendanceStats(),
            'feeBalance' => $this->getFeeBalance(),
            'todaySchedule' => $this->getTodaySchedule(),
        ];

        return view('modules/student/dashboard/index', $data);
    }

    /**
     * Get student information.
     */
    private function getStudentInfo(): ?object
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
     * Get classes enrolled by this student.
     */
    private function getMyClasses(): array
    {
        $db = \Config\Database::connect();

        return $db->table('student_enrollments')
            ->select('school_classes.*, student_enrollments.enrollment_date')
            ->join('school_classes', 'student_enrollments.class_id = school_classes.id')
            ->where('student_enrollments.student_id', $this->userID)
            ->get()
            ->getResultArray() ?? [];
    }

    /**
     * Get pending assignments.
     */
    private function getPendingAssignments(): array
    {
        $db = \Config\Database::connect();

        return $db->table('assignments')
            ->select('assignments.*, school_classes.name as class_name, courses.name as course_name')
            ->join('student_enrollments', 'assignments.class_id = student_enrollments.class_id')
            ->join('school_classes', 'assignments.class_id = school_classes.id', 'left')
            ->join('courses', 'assignments.course_id = courses.id', 'left')
            ->where('student_enrollments.student_id', $this->userID)
            ->where('assignments.due_date >=', date('Y-m-d'))
            ->orderBy('assignments.due_date', 'ASC')
            ->limit(5)
            ->get()
            ->getResultArray() ?? [];
    }

    /**
     * Get recent grades.
     */
    private function getRecentGrades(): array
    {
        $db = \Config\Database::connect();

        return $db->table('grades')
            ->select('grades.*, courses.name as course_name, courses.code as course_code')
            ->join('courses', 'grades.course_id = courses.id', 'left')
            ->where('grades.student_id', $this->userID)
            ->orderBy('grades.created_at', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray() ?? [];
    }

    /**
     * Get attendance statistics.
     */
    private function getAttendanceStats(): array
    {
        // Placeholder - implement when attendance module is ready
        return [
            'total_days' => 0,
            'present_days' => 0,
            'absent_days' => 0,
            'attendance_rate' => 0,
        ];
    }

    /**
     * Get fee balance.
     */
    private function getFeeBalance(): array
    {
        $db = \Config\Database::connect();

        // Total invoiced
        $totalInvoiced = $db->table('invoices')
            ->selectSum('amount', 'total')
            ->where('student_id', $this->userID)
            ->get()
            ->getRow()
            ->total ?? 0;

        // Total paid
        $totalPaid = $db->table('payments')
            ->selectSum('amount', 'total')
            ->where('student_id', $this->userID)
            ->where('status', 'completed')
            ->get()
            ->getRow()
            ->total ?? 0;

        return [
            'total_invoiced' => $totalInvoiced,
            'total_paid' => $totalPaid,
            'balance' => $totalInvoiced - $totalPaid,
        ];
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
