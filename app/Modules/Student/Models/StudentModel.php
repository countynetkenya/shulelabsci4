<?php

namespace App\Modules\Student\Models;

use CodeIgniter\Model;

/**
 * StudentModel - Manages the students table
 * 
 * Columns (from migration):
 * - id, school_id, student_id, first_name, last_name, class_id, admission_number,
 *   date_of_birth, gender, status, parent_name, parent_phone, parent_email,
 *   created_at, updated_at
 */
class StudentModel extends Model
{
    protected $table = 'students';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'school_id',
        'student_id',
        'first_name',
        'last_name',
        'class_id',
        'admission_number',
        'date_of_birth',
        'gender',
        'status',
        'parent_name',
        'parent_phone',
        'parent_email',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation rules
    protected $validationRules = [
        'school_id'        => 'required|integer',
        'first_name'       => 'required|min_length[2]|max_length[100]',
        'last_name'        => 'required|min_length[2]|max_length[100]',
        'admission_number' => 'permit_empty|max_length[50]',
        'gender'           => 'permit_empty|in_list[male,female,other]',
        'status'           => 'permit_empty|in_list[active,inactive,graduated,transferred,suspended]',
        'parent_phone'     => 'permit_empty|max_length[20]',
        'parent_email'     => 'permit_empty|valid_email|max_length[100]',
    ];

    protected $validationMessages = [
        'first_name' => [
            'required' => 'First name is required.',
            'min_length' => 'First name must be at least 2 characters.',
        ],
        'last_name' => [
            'required' => 'Last name is required.',
            'min_length' => 'Last name must be at least 2 characters.',
        ],
    ];

    /**
     * Get students by school with optional filters
     */
    public function getStudentsBySchool(int $schoolId, array $filters = []): array
    {
        $builder = $this->where('school_id', $schoolId);

        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        if (!empty($filters['class_id'])) {
            $builder->where('class_id', $filters['class_id']);
        }

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('first_name', $filters['search'])
                ->orLike('last_name', $filters['search'])
                ->orLike('admission_number', $filters['search'])
                ->groupEnd();
        }

        return $builder->orderBy('first_name', 'ASC')
                      ->orderBy('last_name', 'ASC')
                      ->findAll();
    }

    /**
     * Get student by ID scoped to school
     */
    public function getStudentById(int $id, int $schoolId): ?array
    {
        $student = $this->where('school_id', $schoolId)
                       ->where('id', $id)
                       ->first();
        
        return $student ?: null;
    }

    /**
     * Get active students count for a school
     */
    public function getActiveCount(int $schoolId): int
    {
        return $this->where('school_id', $schoolId)
                   ->where('status', 'active')
                   ->countAllResults();
    }

    /**
     * Get unique classes from students in a school
     */
    public function getClasses(int $schoolId): array
    {
        return $this->select('DISTINCT class_id as class_id', false)
            ->where('school_id', $schoolId)
            ->where('class_id IS NOT NULL')
            ->findAll();
    }
}
