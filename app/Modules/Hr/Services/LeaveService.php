<?php

namespace App\Modules\Hr\Services;

use CodeIgniter\Database\ConnectionInterface;
use Config\Database;

/**
 * LeaveService - Handles leave request management.
 */
class LeaveService
{
    private $db;

    public function __construct(?ConnectionInterface $connection = null)
    {
        $this->db = $connection ?? Database::connect();
    }

    /**
     * Submit a leave request.
     */
    public function submitRequest(int $employeeId, int $leaveTypeId, string $startDate, string $endDate, ?string $reason = null): int
    {
        $schoolId = session('school_id');

        // Calculate days
        $days = $this->calculateLeaveDays($startDate, $endDate);

        // Check balance
        $balance = $this->getBalance($employeeId, $leaveTypeId);
        if ($balance['remaining_days'] < $days) {
            throw new \RuntimeException("Insufficient leave balance. Available: {$balance['remaining_days']} days");
        }

        $this->db->table('leave_requests')->insert([
            'school_id' => $schoolId,
            'employee_id' => $employeeId,
            'leave_type_id' => $leaveTypeId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days_requested' => $days,
            'reason' => $reason,
            'status' => 'pending',
        ]);

        return (int) $this->db->insertID();
    }

    /**
     * Approve a leave request.
     */
    public function approve(int $requestId, int $approvedBy): bool
    {
        $request = $this->db->table('leave_requests')
            ->where('id', $requestId)
            ->get()
            ->getRowArray();

        if (!$request || $request['status'] !== 'pending') {
            return false;
        }

        $this->db->transStart();

        // Update request status
        $this->db->table('leave_requests')
            ->where('id', $requestId)
            ->update([
                'status' => 'approved',
                'approved_by' => $approvedBy,
                'approved_at' => date('Y-m-d H:i:s'),
            ]);

        // Update balance
        $this->deductBalance($request['employee_id'], $request['leave_type_id'], $request['days_requested']);

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Reject a leave request.
     */
    public function reject(int $requestId, int $rejectedBy, string $reason): bool
    {
        return $this->db->table('leave_requests')
            ->where('id', $requestId)
            ->where('status', 'pending')
            ->update([
                'status' => 'rejected',
                'approved_by' => $rejectedBy,
                'approved_at' => date('Y-m-d H:i:s'),
                'rejection_reason' => $reason,
            ]);
    }

    /**
     * Get leave balance for an employee.
     */
    public function getBalance(int $employeeId, int $leaveTypeId, ?int $year = null): array
    {
        $year = $year ?? date('Y');

        $balance = $this->db->table('leave_balances')
            ->where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('year', $year)
            ->get()
            ->getRowArray();

        if (!$balance) {
            // Initialize balance from leave type
            $leaveType = $this->db->table('leave_types')
                ->where('id', $leaveTypeId)
                ->get()
                ->getRowArray();

            if ($leaveType) {
                $this->db->table('leave_balances')->insert([
                    'employee_id' => $employeeId,
                    'leave_type_id' => $leaveTypeId,
                    'year' => $year,
                    'entitled_days' => $leaveType['days_per_year'],
                    'taken_days' => 0,
                    'carried_forward' => 0,
                    'remaining_days' => $leaveType['days_per_year'],
                ]);

                return $this->getBalance($employeeId, $leaveTypeId, $year);
            }

            return [
                'entitled_days' => 0,
                'taken_days' => 0,
                'remaining_days' => 0,
            ];
        }

        return $balance;
    }

    /**
     * Get all leave balances for an employee.
     */
    public function getAllBalances(int $employeeId, ?int $year = null): array
    {
        $year = $year ?? date('Y');

        return $this->db->table('leave_balances lb')
            ->select('lb.*, lt.name as leave_type_name, lt.code as leave_type_code')
            ->join('leave_types lt', 'lt.id = lb.leave_type_id')
            ->where('lb.employee_id', $employeeId)
            ->where('lb.year', $year)
            ->get()
            ->getResultArray();
    }

    /**
     * Get pending requests for approval.
     */
    public function getPendingRequests(?int $schoolId = null): array
    {
        $schoolId = $schoolId ?? session('school_id');

        return $this->db->table('leave_requests lr')
            ->select('lr.*, e.employee_number, u.first_name, u.last_name, lt.name as leave_type_name')
            ->join('employees e', 'e.id = lr.employee_id')
            ->join('users u', 'u.id = e.user_id')
            ->join('leave_types lt', 'lt.id = lr.leave_type_id')
            ->where('lr.school_id', $schoolId)
            ->where('lr.status', 'pending')
            ->orderBy('lr.created_at', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Calculate leave days between dates.
     */
    private function calculateLeaveDays(string $startDate, string $endDate): float
    {
        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $interval = $start->diff($end);
        return $interval->days + 1;
    }

    /**
     * Deduct from leave balance.
     */
    private function deductBalance(int $employeeId, int $leaveTypeId, float $days): void
    {
        $year = date('Y');

        $this->db->table('leave_balances')
            ->where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('year', $year)
            ->set('taken_days', 'taken_days + ' . $days, false)
            ->set('remaining_days', 'remaining_days - ' . $days, false)
            ->update();
    }
}
