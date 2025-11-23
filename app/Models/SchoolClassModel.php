<?php

namespace App\Models;

/**
 * SchoolClassModel - Manages school classes.
 */
class SchoolClassModel extends TenantModel
{
    protected $table            = 'school_classes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'school_id',
        'class_name',
        'grade_level',
        'section',
        'class_teacher_id',
        'academic_year',
        'max_capacity',
        'room_number',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'school_id'  => 'required|integer',
        'class_name' => 'required|min_length[1]|max_length[100]',
    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;

    /**
     * Get class with teacher details.
     */
    public function getClassWithTeacher(int $classId): ?array
    {
        return $this->select('school_classes.*, ci4_users.first_name, ci4_users.last_name, ci4_users.email')
            ->join('ci4_users', 'ci4_users.id = school_classes.class_teacher_id', 'left')
            ->find($classId);
    }

    /**
     * Get classes for a teacher.
     */
    public function getTeacherClasses(int $teacherId): array
    {
        return $this->where('class_teacher_id', $teacherId)->findAll();
    }

    /**
     * Get student count for a class.
     */
    public function getStudentCount(int $classId): int
    {
        return $this->db->table('student_enrollments')
            ->where('class_id', $classId)
            ->where('status', 'active')
            ->countAllResults();
    }
}
