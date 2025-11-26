<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

/**
 * SuperAdmin Dashboard Controller
 * 
 * Provides system-wide overview for superadmin users
 */
class Dashboard extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Display superadmin dashboard
     */
    public function index()
    {
        // Check if user is superadmin
        if (!$this->isSuperAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'Access denied. SuperAdmin privileges required.');
        }

        $data = [
            'title' => 'SuperAdmin Dashboard',
            'totalSchools' => $this->getTotalSchools(),
            'totalUsers' => $this->getTotalUsers(),
            'totalStudents' => $this->getTotalStudents(),
            'totalTeachers' => $this->getTotalTeachers(),
            'recentActivity' => $this->getRecentActivity(),
            'systemStats' => $this->getSystemStats(),
        ];

        return view('admin/dashboard/index', $data);
    }

    /**
     * Get total number of schools
     */
    private function getTotalSchools(): int
    {
        $db = \Config\Database::connect();
        return (int) $db->table('schools')->countAllResults();
    }

    /**
     * Get total number of users
     */
    private function getTotalUsers(): int
    {
        return (int) $this->userModel->countAllResults();
    }

    /**
     * Get total number of students
     */
    private function getTotalStudents(): int
    {
        $db = \Config\Database::connect();
        return (int) $db->table('ci4_users')
            ->join('ci4_user_roles', 'ci4_users.id = ci4_user_roles.user_id')
            ->join('ci4_roles', 'ci4_user_roles.role_id = ci4_roles.id')
            ->where('ci4_roles.name', 'student')
            ->countAllResults();
    }

    /**
     * Get total number of teachers
     */
    private function getTotalTeachers(): int
    {
        $db = \Config\Database::connect();
        return (int) $db->table('ci4_users')
            ->join('ci4_user_roles', 'ci4_users.id = ci4_user_roles.user_id')
            ->join('ci4_roles', 'ci4_user_roles.role_id = ci4_roles.id')
            ->where('ci4_roles.name', 'teacher')
            ->countAllResults();
    }

    /**
     * Get recent activity across all schools
     */
    private function getRecentActivity(): array
    {
        $db = \Config\Database::connect();
        
        // Get recent logins
        $recentLogins = $db->table('ci4_users')
            ->select('ci4_users.username, ci4_users.email, ci4_users.last_login')
            ->orderBy('ci4_users.last_login', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        return $recentLogins ?? [];
    }

    /**
     * Get system statistics
     */
    private function getSystemStats(): array
    {
        $db = \Config\Database::connect();
        
        return [
            'active_sessions' => 0, // Placeholder
            'total_assignments' => $db->table('assignments')->countAllResults(),
            'total_library_books' => $db->table('library_books')->countAllResults(),
            'pending_payments' => $db->table('invoices')
                ->where('status', 'pending')
                ->countAllResults(),
        ];
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
