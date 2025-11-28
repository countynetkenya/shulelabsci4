<?php

namespace App\Modules\Learning\Services;

use CodeIgniter\Database\ConnectionInterface;
use Config\Database;

/**
 * AttendanceService - Handles student attendance tracking.
 */
class AttendanceService
{
    private $db;

    public function __construct(?ConnectionInterface $connection = null)
    {
        $this->db = $connection ?? Database::connect();
    }

    /**
     * Mark attendance for a student.
     */
    public function markAttendance(
        int $studentId,
        int $classId,
        string $date,
        string $status,
        ?string $checkInTime = null,
        ?string $remarks = null,
        ?int $markedBy = null
    ): int {
        $schoolId = session('school_id');

        // Check for existing record
        $existing = $this->db->table('attendance')
            ->where('student_id', $studentId)
            ->where('attendance_date', $date)
            ->get()
            ->getRowArray();

        $data = [
            'school_id' => $schoolId,
            'student_id' => $studentId,
            'class_id' => $classId,
            'attendance_date' => $date,
            'status' => $status,
            'check_in_time' => $checkInTime,
            'remarks' => $remarks,
            'marked_by' => $markedBy ?? session('user_id'),
        ];

        if ($existing) {
            $this->db->table('attendance')
                ->where('id', $existing['id'])
                ->update($data);
            return (int) $existing['id'];
        }

        $this->db->table('attendance')->insert($data);
        return (int) $this->db->insertID();
    }

    /**
     * Mark bulk attendance for a class.
     */
    public function markBulkAttendance(int $classId, string $date, array $attendanceData): int
    {
        $count = 0;
        foreach ($attendanceData as $studentId => $status) {
            $this->markAttendance($studentId, $classId, $date, $status);
            $count++;
        }
        return $count;
    }

    /**
     * Get attendance for a class on a specific date.
     */
    public function getClassAttendance(int $classId, string $date): array
    {
        return $this->db->table('attendance a')
            ->select('a.*, u.first_name, u.last_name')
            ->join('users u', 'u.id = a.student_id')
            ->where('a.class_id', $classId)
            ->where('a.attendance_date', $date)
            ->get()
            ->getResultArray();
    }

    /**
     * Get student attendance summary for a date range.
     */
    public function getStudentSummary(int $studentId, string $startDate, string $endDate): array
    {
        $result = $this->db->table('attendance')
            ->select('status, COUNT(*) as count')
            ->where('student_id', $studentId)
            ->where('attendance_date >=', $startDate)
            ->where('attendance_date <=', $endDate)
            ->groupBy('status')
            ->get()
            ->getResultArray();

        $summary = [
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'excused' => 0,
            'half_day' => 0,
            'total_days' => 0,
            'attendance_percentage' => 0,
        ];

        foreach ($result as $row) {
            $summary[$row['status']] = (int) $row['count'];
            $summary['total_days'] += (int) $row['count'];
        }

        if ($summary['total_days'] > 0) {
            $presentDays = $summary['present'] + $summary['late'] + ($summary['half_day'] * 0.5);
            $summary['attendance_percentage'] = round(($presentDays / $summary['total_days']) * 100, 2);
        }

        return $summary;
    }

    /**
     * Get class attendance report.
     */
    public function getClassReport(int $classId, string $startDate, string $endDate): array
    {
        return $this->db->table('attendance a')
            ->select('a.student_id, u.first_name, u.last_name,
                      SUM(CASE WHEN a.status = "present" THEN 1 ELSE 0 END) as present_count,
                      SUM(CASE WHEN a.status = "absent" THEN 1 ELSE 0 END) as absent_count,
                      SUM(CASE WHEN a.status = "late" THEN 1 ELSE 0 END) as late_count,
                      COUNT(*) as total_days')
            ->join('users u', 'u.id = a.student_id')
            ->where('a.class_id', $classId)
            ->where('a.attendance_date >=', $startDate)
            ->where('a.attendance_date <=', $endDate)
            ->groupBy('a.student_id')
            ->get()
            ->getResultArray();
    }
}
