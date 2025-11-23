<?php

namespace App\Services;

use App\Models\SchoolClassModel;
use App\Models\SchoolUserModel;
use App\Models\StudentEnrollmentModel;

/**
 * EnrollmentService - Student enrollment management business logic.
 */
class EnrollmentService
{
    protected StudentEnrollmentModel $enrollmentModel;

    protected SchoolClassModel $classModel;

    protected SchoolUserModel $schoolUserModel;

    public function __construct()
    {
        $this->enrollmentModel = model(StudentEnrollmentModel::class);
        $this->classModel = model(SchoolClassModel::class);
        $this->schoolUserModel = model(SchoolUserModel::class);
    }

    /**
     * Enroll a student in a class.
     */
    public function enrollStudent(int $schoolId, int $studentId, int $classId, ?int $parentId = null): array
    {
        // Verify class belongs to school
        $class = $this->classModel->forSchool($schoolId)->find($classId);

        if (!$class) {
            return ['success' => false, 'message' => 'Class not found or does not belong to this school'];
        }

        // Check class capacity
        $currentEnrollments = $this->enrollmentModel
            ->where('class_id', $classId)
            ->where('status', 'active')
            ->countAllResults();

        if ($currentEnrollments >= $class['max_capacity']) {
            return ['success' => false, 'message' => 'Class is at full capacity'];
        }

        // Check if student is already enrolled
        $existing = $this->enrollmentModel->forSchool($schoolId)
            ->where('student_id', $studentId)
            ->where('status', 'active')
            ->first();

        if ($existing) {
            return ['success' => false, 'message' => 'Student is already enrolled in this school'];
        }

        // Create enrollment
        $data = [
            'school_id' => $schoolId,
            'student_id' => $studentId,
            'class_id' => $classId,
            'parent_id' => $parentId,
            'enrollment_date' => date('Y-m-d'),
            'status' => 'active',
        ];

        $enrollmentId = $this->enrollmentModel->insert($data);

        if (!$enrollmentId) {
            return ['success' => false, 'message' => 'Failed to create enrollment'];
        }

        return [
            'success' => true,
            'enrollment_id' => $enrollmentId,
            'message' => 'Student enrolled successfully',
        ];
    }

    /**
     * Transfer student to different class.
     */
    public function transferClass(int $enrollmentId, int $newClassId): array
    {
        $enrollment = $this->enrollmentModel->find($enrollmentId);

        if (!$enrollment) {
            return ['success' => false, 'message' => 'Enrollment not found'];
        }

        // Verify new class exists and has capacity
        $newClass = $this->classModel->forSchool($enrollment['school_id'])->find($newClassId);

        if (!$newClass) {
            return ['success' => false, 'message' => 'Target class not found'];
        }

        $newClassEnrollments = $this->enrollmentModel
            ->where('class_id', $newClassId)
            ->where('status', 'active')
            ->countAllResults();

        if ($newClassEnrollments >= $newClass['max_capacity']) {
            return ['success' => false, 'message' => 'Target class is at full capacity'];
        }

        // Update enrollment
        $updated = $this->enrollmentModel->update($enrollmentId, ['class_id' => $newClassId]);

        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to transfer student'];
        }

        return ['success' => true, 'message' => 'Student transferred successfully'];
    }

    /**
     * Get enrollments for a class.
     */
    public function getClassEnrollments(int $classId): array
    {
        return $this->enrollmentModel
            ->select('student_enrollments.*, users.username, users.email, users.full_name')
            ->join('users', 'users.id = student_enrollments.student_id')
            ->where('student_enrollments.class_id', $classId)
            ->where('student_enrollments.status', 'active')
            ->findAll();
    }

    /**
     * Get student enrollments.
     */
    public function getStudentEnrollments(int $studentId, int $schoolId): array
    {
        return $this->enrollmentModel
            ->select('student_enrollments.*, school_classes.grade_level, school_classes.section')
            ->join('school_classes', 'school_classes.id = student_enrollments.class_id')
            ->where('student_enrollments.student_id', $studentId)
            ->where('student_enrollments.school_id', $schoolId)
            ->findAll();
    }

    /**
     * Withdraw student from school.
     */
    public function withdrawStudent(int $enrollmentId, string $reason = ''): array
    {
        $enrollment = $this->enrollmentModel->find($enrollmentId);

        if (!$enrollment) {
            return ['success' => false, 'message' => 'Enrollment not found'];
        }

        $updated = $this->enrollmentModel->update($enrollmentId, [
            'status' => 'withdrawn',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to withdraw student'];
        }

        return ['success' => true, 'message' => 'Student withdrawn successfully'];
    }
}
