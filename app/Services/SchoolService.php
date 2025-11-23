<?php

namespace App\Services;

use App\Models\SchoolModel;
use App\Models\SchoolClassModel;
use App\Models\StudentEnrollmentModel;

/**
 * SchoolService - Business logic for school management operations.
 */
class SchoolService
{
    protected SchoolModel $schoolModel;
    protected SchoolClassModel $classModel;
    protected StudentEnrollmentModel $enrollmentModel;

    public function __construct()
    {
        $this->schoolModel = model(SchoolModel::class);
        $this->classModel = model(SchoolClassModel::class);
        $this->enrollmentModel = model(StudentEnrollmentModel::class);
    }

    /**
     * Get school dashboard statistics.
     */
    public function getDashboardStats(int $schoolId): array
    {
        $school = $this->schoolModel->find($schoolId);
        
        if (!$school) {
            throw new \RuntimeException('School not found');
        }

        // Get counts
        $totalClasses = $this->classModel->forSchool($schoolId)->countAllResults();
        $totalEnrollments = $this->enrollmentModel->forSchool($schoolId)
            ->where('status', 'active')
            ->countAllResults();
        
        // Get capacity utilization
        $classes = $this->classModel->forSchool($schoolId)->findAll();
        $totalCapacity = array_sum(array_column($classes, 'max_capacity'));
        $utilization = $totalCapacity > 0 ? ($totalEnrollments / $totalCapacity) * 100 : 0;

        return [
            'school' => $school,
            'total_classes' => $totalClasses,
            'total_students' => $totalEnrollments,
            'total_capacity' => $totalCapacity,
            'utilization_percent' => round($utilization, 2),
            'subscription_tier' => $school['subscription_tier'] ?? 'Free',
            'is_active' => (bool)($school['is_active'] ?? false),
        ];
    }

    /**
     * Get school overview with class breakdown.
     */
    public function getSchoolOverview(int $schoolId): array
    {
        $stats = $this->getDashboardStats($schoolId);
        
        // Get classes with enrollment counts
        $classes = $this->classModel->forSchool($schoolId)
            ->select('school_classes.*, COUNT(student_enrollments.id) as student_count')
            ->join('student_enrollments', 'student_enrollments.class_id = school_classes.id', 'left')
            ->where('student_enrollments.status', 'active')
            ->groupBy('school_classes.id')
            ->findAll();

        $stats['classes'] = $classes;

        return $stats;
    }

    /**
     * Check if school can enroll more students.
     */
    public function canEnrollStudents(int $schoolId, int $count = 1): bool
    {
        $school = $this->schoolModel->find($schoolId);
        
        if (!$school) {
            return false;
        }

        $currentEnrollments = $this->enrollmentModel->forSchool($schoolId)
            ->where('status', 'active')
            ->countAllResults();

        $maxStudents = $school['max_students'] ?? 0;

        return ($currentEnrollments + $count) <= $maxStudents;
    }

    /**
     * Get available schools for selection.
     */
    public function getAvailableSchools(): array
    {
        return $this->schoolModel->getActiveSchools();
    }
}
