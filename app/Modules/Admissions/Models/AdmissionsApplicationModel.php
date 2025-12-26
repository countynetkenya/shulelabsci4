<?php

namespace App\Modules\Admissions\Models;

use CodeIgniter\Model;

/**
 * AdmissionsApplicationModel - Manages the applications table.
 *
 * Schema: application_number, academic_year, term, class_applied, student info, parent info, status, etc.
 */
class AdmissionsApplicationModel extends Model
{
    protected $table = 'applications';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = false;

    protected $protectFields = true;

    protected $allowedFields = [
        'school_id',
        'application_number',
        'academic_year',
        'term',
        'class_applied',
        'student_first_name',
        'student_last_name',
        'student_dob',
        'student_gender',
        'previous_school',
        'parent_first_name',
        'parent_last_name',
        'parent_email',
        'parent_phone',
        'parent_relationship',
        'address',
        'status',
        'stage_id',
        'reviewed_by',
        'reviewed_at',
        'decision_notes',
        'application_fee_paid',
        'fee_payment_ref',
    ];

    protected $useTimestamps = true;

    protected $dateFormat = 'datetime';

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';

    // Validation rules
    protected $validationRules = [
        'school_id' => 'required|integer',
        'application_number' => 'required|max_length[50]',
        'academic_year' => 'required|max_length[20]',
        'class_applied' => 'required|integer',
        'student_first_name' => 'required|max_length[100]',
        'student_last_name' => 'required|max_length[100]',
        'student_dob' => 'required|valid_date',
        'student_gender' => 'required|in_list[male,female,other]',
        'parent_first_name' => 'required|max_length[100]',
        'parent_last_name' => 'required|max_length[100]',
        'parent_email' => 'required|valid_email|max_length[255]',
        'parent_phone' => 'required|max_length[20]',
        'parent_relationship' => 'required|in_list[father,mother,guardian]',
        'status' => 'permit_empty|in_list[submitted,under_review,interview_scheduled,test_scheduled,accepted,rejected,waitlisted,enrolled]',
    ];

    protected $validationMessages = [
        'student_first_name' => [
            'required' => 'Student first name is required.',
        ],
        'parent_email' => [
            'required' => 'Parent email is required.',
            'valid_email' => 'Please provide a valid email address.',
        ],
    ];

    /**
     * Get applications by school with optional filters.
     */
    public function getApplicationsBySchool(int $schoolId, array $filters = []): array
    {
        $builder = $this->where('school_id', $schoolId);

        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        if (!empty($filters['academic_year'])) {
            $builder->where('academic_year', $filters['academic_year']);
        }

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('student_first_name', $filters['search'])
                ->orLike('student_last_name', $filters['search'])
                ->orLike('application_number', $filters['search'])
                ->groupEnd();
        }

        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Generate next application number for school.
     */
    public function generateApplicationNumber(int $schoolId, string $academicYear): string
    {
        $count = $this->where('school_id', $schoolId)
            ->where('academic_year', $academicYear)
            ->countAllResults();

        $prefix = 'APP-' . $schoolId . '-' . $academicYear . '-';
        return $prefix . str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get application statistics for a school.
     */
    public function getStatistics(int $schoolId, ?string $academicYear = null): array
    {
        $builder = $this->selectCount('id', 'total')
            ->where('school_id', $schoolId);

        if ($academicYear) {
            $builder->where('academic_year', $academicYear);
        }

        $total = $builder->get()->getRowArray()['total'] ?? 0;

        // Get counts by status
        $statusCounts = $this->select('status, COUNT(*) as count')
            ->where('school_id', $schoolId)
            ->groupBy('status')
            ->findAll();

        return [
            'total' => $total,
            'by_status' => array_column($statusCounts, 'count', 'status'),
        ];
    }
}
