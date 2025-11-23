<?php

namespace App\Models;

/**
 * StudentEnrollmentModel - Manages student enrollments in schools.
 */
class StudentEnrollmentModel extends TenantModel
{
    protected $table            = 'student_enrollments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'student_id',
        'school_id',
        'class_id',
        'enrollment_date',
        'status',
        'admission_number',
        'parent_id',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'student_id'      => 'required|integer',
        'school_id'       => 'required|integer',
        'enrollment_date' => 'required|valid_date',
        'status'          => 'in_list[active,suspended,graduated,transferred,withdrawn]',
    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;

    /**
     * Get enrollment with student and parent details.
     */
    public function getEnrollmentDetails(int $enrollmentId): ?array
    {
        return $this->select('student_enrollments.*, 
                              students.first_name as student_first_name,
                              students.last_name as student_last_name,
                              students.email as student_email,
                              parents.first_name as parent_first_name,
                              parents.last_name as parent_last_name,
                              parents.email as parent_email,
                              school_classes.class_name')
            ->join('users as students', 'students.id = student_enrollments.student_id')
            ->join('users as parents', 'parents.id = student_enrollments.parent_id', 'left')
            ->join('school_classes', 'school_classes.id = student_enrollments.class_id', 'left')
            ->find($enrollmentId);
    }

    /**
     * Get active enrollment for a student in current school.
     */
    public function getStudentEnrollment(int $studentId): ?array
    {
        return $this->where([
            'student_id' => $studentId,
            'status'     => 'active',
        ])->first();
    }

    /**
     * Get all students in a class.
     */
    public function getClassStudents(int $classId, string $status = 'active'): array
    {
        return $this->select('student_enrollments.*, users.first_name, users.last_name, users.email')
            ->join('users', 'users.id = student_enrollments.student_id')
            ->where('student_enrollments.class_id', $classId)
            ->where('student_enrollments.status', $status)
            ->findAll();
    }

    /**
     * Get children for a parent in current school.
     */
    public function getParentChildren(int $parentId): array
    {
        return $this->select('student_enrollments.*, 
                              users.first_name, 
                              users.last_name, 
                              users.email,
                              school_classes.class_name')
            ->join('users', 'users.id = student_enrollments.student_id')
            ->join('school_classes', 'school_classes.id = student_enrollments.class_id', 'left')
            ->where('student_enrollments.parent_id', $parentId)
            ->where('student_enrollments.status', 'active')
            ->findAll();
    }

    /**
     * Enroll student in school.
     */
    public function enrollStudent(array $data): bool
    {
        if (! isset($data['enrollment_date'])) {
            $data['enrollment_date'] = date('Y-m-d');
        }

        if (! isset($data['status'])) {
            $data['status'] = 'active';
        }

        return $this->insert($data) !== false;
    }

    /**
     * Transfer student to another school.
     */
    public function transferStudent(int $studentId, int $fromSchoolId, int $toSchoolId): bool
    {
        // Mark current enrollment as transferred
        $this->where([
            'student_id' => $studentId,
            'school_id'  => $fromSchoolId,
            'status'     => 'active',
        ])->set(['status' => 'transferred'])->update();

        // This would typically be followed by creating new enrollment in target school
        return true;
    }
}
