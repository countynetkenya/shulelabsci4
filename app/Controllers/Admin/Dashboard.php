<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

/**
 * SuperAdmin Dashboard Controller.
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
     * Display superadmin dashboard.
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
     * Get total number of schools.
     */
    private function getTotalSchools(): int
    {
        $db = \Config\Database::connect();
        return (int) $db->table('schools')->countAllResults();
    }

    /**
     * Get total number of users.
     */
    private function getTotalUsers(): int
    {
        return (int) $this->userModel->countAllResults();
    }

    /**
     * Get total number of students.
     */
    private function getTotalStudents(): int
    {
        $db = \Config\Database::connect();
        return (int) $db->table('users')
            ->join('user_roles', 'users.id = user_roles.user_id')
            ->join('roles', 'user_roles.role_id = roles.id')
            ->where('roles.name', 'student')
        ->countAllResults();
    }

    /**
     * Get total number of teachers.
     */
    private function getTotalTeachers(): int
    {
        $db = \Config\Database::connect();
        return (int) $db->table('users')
            ->join('user_roles', 'users.id = user_roles.user_id')
            ->join('roles', 'user_roles.role_id = roles.id')
            ->where('roles.name', 'teacher')
        ->countAllResults();
    }

    /**
     * Get recent activity across all schools.
     */
    private function getRecentActivity(): array
    {
        $db = \Config\Database::connect();

        // Get recent logins
        $recentLogins = $db->table('users')
            ->select('users.username, users.email, users.last_login')
            ->orderBy('users.last_login', 'DESC')
        ->limit(10)
        ->get()
        ->getResultArray();

        return $recentLogins ?? [];
    }

    /**
     * Get system statistics.
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
     * Check if current user is superadmin.
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
