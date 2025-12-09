<?php

namespace App\Modules\Teacher\Models;

use CodeIgniter\Model;

/**
 * TeacherModel - Manages the teachers table
 */
class TeacherModel extends Model
{
    protected $table = 'teachers';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'school_id',
        'teacher_id',
        'first_name',
        'last_name',
        'employee_id',
        'department',
        'subjects',
        'qualification',
        'date_of_joining',
        'status',
        'phone',
        'email',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation rules
    protected $validationRules = [
        'school_id'   => 'required|integer',
        'first_name'  => 'required|min_length[2]|max_length[100]',
        'last_name'   => 'required|min_length[2]|max_length[100]',
        'employee_id' => 'permit_empty|max_length[50]',
        'status'      => 'permit_empty|in_list[active,inactive,on_leave,terminated]',
        'email'       => 'permit_empty|valid_email|max_length[100]',
        'phone'       => 'permit_empty|max_length[20]',
    ];

    protected $validationMessages = [
        'first_name' => [
            'required'   => 'First name is required.',
            'min_length' => 'First name must be at least 2 characters.',
        ],
        'last_name' => [
            'required'   => 'Last name is required.',
            'min_length' => 'Last name must be at least 2 characters.',
        ],
    ];

    /**
     * Get teachers by school with optional filters
     */
    public function getTeachersBySchool(int $schoolId, array $filters = []): array
    {
        $builder = $this->where('school_id', $schoolId);

        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        if (!empty($filters['department'])) {
            $builder->where('department', $filters['department']);
        }

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('first_name', $filters['search'])
                ->orLike('last_name', $filters['search'])
                ->orLike('employee_id', $filters['search'])
                ->orLike('email', $filters['search'])
                ->groupEnd();
        }

        return $builder->orderBy('first_name', 'ASC')
                      ->orderBy('last_name', 'ASC')
                      ->findAll();
    }

    /**
     * Get teacher by ID scoped to school
     */
    public function getTeacherById(int $id, int $schoolId): ?array
    {
        $teacher = $this->where('school_id', $schoolId)
                       ->where('id', $id)
                       ->first();
        
        return $teacher ?: null;
    }

    /**
     * Get active teachers count for a school
     */
    public function getActiveCount(int $schoolId): int
    {
        return $this->where('school_id', $schoolId)
                   ->where('status', 'active')
                   ->countAllResults();
    }

    /**
     * Get unique departments from teachers in a school
     */
    public function getDepartments(int $schoolId): array
    {
        return $this->select('DISTINCT department as department', false)
            ->where('school_id', $schoolId)
            ->where('department IS NOT NULL')
            ->where('department !=', '')
            ->findAll();
    }
}
