<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;

/**
 * Admin Dashboard Controller
 * 
 * School-specific dashboard for admin users
 */
class Dashboard extends BaseController
{
    protected $schoolID;

    public function __construct()
    {
        $this->schoolID = session()->get('schoolID');
    }

    /**
     * Display admin dashboard for specific school
     */
    public function index()
    {
        if (!$this->schoolID) {
            return redirect()->to('/school/select')->with('error', 'Please select a school first.');
        }

        $data = [
            'title' => 'Admin Dashboard',
            'schoolInfo' => $this->getSchoolInfo(),
            'totalStudents' => $this->getTotalStudents(),
            'totalTeachers' => $this->getTotalTeachers(),
            'totalClasses' => $this->getTotalClasses(),
            'feeCollection' => $this->getFeeCollectionStats(),
            'recentActivity' => $this->getRecentActivity(),
            'attendanceStats' => $this->getAttendanceStats(),
        ];

        return view('modules/admin/dashboard/index', $data);
    }

    /**
     * Get school information
     */
    private function getSchoolInfo(): ?object
    {
        $db = \Config\Database::connect();
        return $db->table('schools')
            ->where('id', $this->schoolID)
            ->get()
            ->getRow();
    }

    /**
     * Get total students in this school
     */
    private function getTotalStudents(): int
    {
        $db = \Config\Database::connect();
        return (int) $db->table('school_users')
              ->join('user_roles', 'school_users.user_id = user_roles.user_id')
              ->join('roles', 'user_roles.role_id = roles.id')
              ->where('school_users.school_id', $this->schoolID)
              ->where('roles.name', 'student')
            ->countAllResults();
    }

    /**
     * Get total teachers in this school
     */
    private function getTotalTeachers(): int
    {
        $db = \Config\Database::connect();
        return (int) $db->table('school_users')
              ->join('user_roles', 'school_users.user_id = user_roles.user_id')
              ->join('roles', 'user_roles.role_id = roles.id')
              ->where('school_users.school_id', $this->schoolID)
              ->where('roles.name', 'teacher')
            ->countAllResults();
    }

    /**
     * Get total classes in this school
     */
    private function getTotalClasses(): int
    {
        $db = \Config\Database::connect();
        return (int) $db->table('school_classes')
            ->where('school_id', $this->schoolID)
            ->countAllResults();
    }

    /**
     * Get fee collection statistics
     */
    private function getFeeCollectionStats(): array
    {
        $db = \Config\Database::connect();
        
        // Total expected fees
        $totalExpected = $db->table('invoices')
            ->selectSum('amount', 'total')
            ->where('school_id', $this->schoolID)
            ->get()
            ->getRow()
            ->total ?? 0;

        // Total collected fees
        $totalCollected = $db->table('payments')
            ->selectSum('amount', 'total')
            ->where('school_id', $this->schoolID)
            ->where('status', 'completed')
            ->get()
            ->getRow()
            ->total ?? 0;

        return [
            'expected' => $totalExpected,
            'collected' => $totalCollected,
            'pending' => $totalExpected - $totalCollected,
            'collection_rate' => $totalExpected > 0 ? round(($totalCollected / $totalExpected) * 100, 2) : 0,
        ];
    }

    /**
     * Get recent activity in this school
     */
    private function getRecentActivity(): array
    {
        // Placeholder - implement based on requirements
        return [];
    }

    /**
     * Get attendance statistics
     */
    private function getAttendanceStats(): array
    {
        // Placeholder - implement when attendance module is ready
        return [
            'today_present' => 0,
            'today_absent' => 0,
            'attendance_rate' => 0,
        ];
    }
}
