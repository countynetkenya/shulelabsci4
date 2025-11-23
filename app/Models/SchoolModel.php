<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * SchoolModel - Manages school/tenant entities.
 */
class SchoolModel extends Model
{
    protected $table            = 'schools';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'school_code',
        'school_name',
        'school_type',
        'country',
        'county',
        'sub_county',
        'address',
        'phone',
        'email',
        'website',
        'logo_url',
        'timezone',
        'currency',
        'academic_year_start',
        'academic_year_end',
        'subscription_plan',
        'subscription_expires_at',
        'max_students',
        'max_teachers',
        'is_active',
        'settings',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'school_code' => 'required|min_length[3]|max_length[20]|is_unique[schools.school_code,id,{id}]',
        'school_name' => 'required|min_length[3]|max_length[255]',
        'school_type' => 'in_list[primary,secondary,mixed,college]',
        'email'       => 'permit_empty|valid_email',
        'website'     => 'permit_empty|valid_url',
    ];

    protected $validationMessages = [
        'school_code' => [
            'required'  => 'School code is required',
            'is_unique' => 'This school code is already in use',
        ],
        'school_name' => [
            'required' => 'School name is required',
        ],
    ];

    protected $skipValidation = false;

    /**
     * Get active schools.
     */
    public function getActiveSchools(): array
    {
        return $this->where('is_active', true)->findAll();
    }

    /**
     * Get school by code.
     */
    public function getByCode(string $code): ?array
    {
        return $this->where('school_code', $code)->first();
    }

    /**
     * Get schools by type.
     */
    public function getByType(string $type): array
    {
        return $this->where('school_type', $type)->findAll();
    }

    /**
     * Get schools by subscription plan.
     */
    public function getBySubscriptionPlan(string $plan): array
    {
        return $this->where('subscription_plan', $plan)->findAll();
    }

    /**
     * Get schools with expired subscriptions.
     */
    public function getExpiredSubscriptions(): array
    {
        return $this->where('subscription_expires_at <', date('Y-m-d H:i:s'))
            ->where('subscription_plan !=', 'free')
            ->findAll();
    }

    /**
     * Get school statistics.
     */
    public function getStatistics(int $schoolId): array
    {
        $db = $this->db;

        // Get student count
        $studentCount = $db->table('student_enrollments')
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->countAllResults();

        // Get teacher count
        $teacherCount = $db->table('school_users')
            ->join('ci4_roles', 'ci4_roles.id = school_users.role_id')
            ->where('school_users.school_id', $schoolId)
            ->where('ci4_roles.name', 'Teacher')
            ->countAllResults();

        // Get class count
        $classCount = $db->table('school_classes')
            ->where('school_id', $schoolId)
            ->countAllResults();

        return [
            'student_count' => $studentCount,
            'teacher_count' => $teacherCount,
            'class_count'   => $classCount,
        ];
    }
}
