<?php

namespace App\Services;

use App\Models\SchoolClassModel;
use App\Models\SchoolUserModel;

/**
 * HrService - Human resources management for multi-school system.
 */
class HrService
{
    protected SchoolUserModel $schoolUserModel;

    protected SchoolClassModel $classModel;

    public function __construct()
    {
        $this->schoolUserModel = model(SchoolUserModel::class);
        $this->classModel = model(SchoolClassModel::class);
    }

    /**
     * Get all staff for a school (teachers, admins, support staff).
     */
    public function getSchoolStaff(int $schoolId, ?string $roleFilter = null): array
    {
        $builder = $this->schoolUserModel
            ->select('school_users.*, users.username, users.email, users.full_name, roles.role_name')
            ->join('users', 'users.id = school_users.user_id')
            ->join('roles', 'roles.id = school_users.role_id')
            ->where('school_users.school_id', $schoolId);

        if ($roleFilter) {
            $builder->where('roles.role_name', $roleFilter);
        } else {
            // Exclude students and parents (get only staff)
            $builder->whereNotIn('roles.role_name', ['Student', 'Parent']);
        }

        return $builder->findAll();
    }

    /**
     * Assign teacher to a school.
     */
    public function assignTeacher(int $userId, int $schoolId, int $roleId, bool $isPrimary = false): array
    {
        // Check if already assigned
        $existing = $this->schoolUserModel
            ->where('user_id', $userId)
            ->where('school_id', $schoolId)
            ->first();

        if ($existing) {
            return ['success' => false, 'message' => 'Teacher already assigned to this school'];
        }

        $data = [
            'user_id' => $userId,
            'school_id' => $schoolId,
            'role_id' => $roleId,
            'is_primary_school' => $isPrimary,
            'joined_at' => date('Y-m-d H:i:s'),
        ];

        $result = $this->schoolUserModel->insert($data);

        if (!$result) {
            return ['success' => false, 'message' => 'Failed to assign teacher'];
        }

        return ['success' => true, 'message' => 'Teacher assigned successfully'];
    }

    /**
     * Remove teacher from school.
     */
    public function removeTeacher(int $userId, int $schoolId): array
    {
        $assignment = $this->schoolUserModel
            ->where('user_id', $userId)
            ->where('school_id', $schoolId)
            ->first();

        if (!$assignment) {
            return ['success' => false, 'message' => 'Assignment not found'];
        }

        $deleted = $this->schoolUserModel->delete($assignment['id']);

        if (!$deleted) {
            return ['success' => false, 'message' => 'Failed to remove teacher'];
        }

        return ['success' => true, 'message' => 'Teacher removed successfully'];
    }

    /**
     * Get teacher's assigned classes.
     */
    public function getTeacherClasses(int $teacherId, int $schoolId): array
    {
        return $this->classModel
            ->forSchool($schoolId)
            ->where('class_teacher_id', $teacherId)
            ->findAll();
    }

    /**
     * Assign teacher to class.
     */
    public function assignTeacherToClass(int $classId, int $teacherId, int $schoolId): array
    {
        // Verify class belongs to school
        $class = $this->classModel->forSchool($schoolId)->find($classId);

        if (!$class) {
            return ['success' => false, 'message' => 'Class not found'];
        }

        // Verify teacher is assigned to this school
        $teacherAssignment = $this->schoolUserModel
            ->where('user_id', $teacherId)
            ->where('school_id', $schoolId)
            ->first();

        if (!$teacherAssignment) {
            return ['success' => false, 'message' => 'Teacher not assigned to this school'];
        }

        $updated = $this->classModel->update($classId, ['class_teacher_id' => $teacherId]);

        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to assign teacher to class'];
        }

        return ['success' => true, 'message' => 'Teacher assigned to class successfully'];
    }

    /**
     * Get staff statistics for school.
     */
    public function getStaffStats(int $schoolId): array
    {
        $allStaff = $this->getSchoolStaff($schoolId);

        $stats = [
            'total_staff' => count($allStaff),
            'by_role' => [],
        ];

        foreach ($allStaff as $staff) {
            $role = $staff['role_name'];
            if (!isset($stats['by_role'][$role])) {
                $stats['by_role'][$role] = 0;
            }
            $stats['by_role'][$role]++;
        }

        return $stats;
    }
}
