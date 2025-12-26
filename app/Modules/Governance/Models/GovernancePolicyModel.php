<?php

namespace App\Modules\Governance\Models;

use CodeIgniter\Model;

/**
 * GovernancePolicyModel - Manages the policies table.
 *
 * Schema: policy_number, title, category, content, summary, version, status, effective_date, etc.
 */
class GovernancePolicyModel extends Model
{
    protected $table = 'policies';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = false;

    protected $protectFields = true;

    protected $allowedFields = [
        'school_id',
        'policy_number',
        'title',
        'category',
        'content',
        'summary',
        'version',
        'status',
        'approved_by_resolution_id',
        'effective_date',
        'review_date',
        'document_url',
        'created_by',
    ];

    protected $useTimestamps = true;

    protected $dateFormat = 'datetime';

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';

    // Validation rules
    protected $validationRules = [
        'school_id' => 'required|integer',
        'policy_number' => 'required|max_length[50]',
        'title' => 'required|min_length[3]|max_length[255]',
        'category' => 'required|max_length[50]',
        'content' => 'required',
        'status' => 'permit_empty|in_list[draft,under_review,approved,archived]',
        'version' => 'permit_empty|max_length[20]',
        'created_by' => 'required|integer',
    ];

    protected $validationMessages = [
        'title' => [
            'required' => 'Policy title is required.',
            'min_length' => 'Policy title must be at least 3 characters.',
        ],
        'content' => [
            'required' => 'Policy content is required.',
        ],
    ];

    /**
     * Get policies by school with optional filters.
     */
    public function getPoliciesBySchool(int $schoolId, array $filters = []): array
    {
        $builder = $this->where('school_id', $schoolId);

        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        if (!empty($filters['category'])) {
            $builder->where('category', $filters['category']);
        }

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('title', $filters['search'])
                ->orLike('policy_number', $filters['search'])
                ->orLike('content', $filters['search'])
                ->groupEnd();
        }

        return $builder->orderBy('effective_date', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Generate next policy number for school.
     */
    public function generatePolicyNumber(int $schoolId, string $category): string
    {
        $count = $this->where('school_id', $schoolId)
            ->where('category', $category)
            ->countAllResults();

        $categoryCode = strtoupper(substr($category, 0, 3));
        return 'POL-' . $schoolId . '-' . $categoryCode . '-' . str_pad((string) ($count + 1), 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get policy categories for a school.
     */
    public function getCategories(int $schoolId): array
    {
        return $this->select('DISTINCT category as category', false)
            ->where('school_id', $schoolId)
            ->where('category IS NOT NULL')
            ->where('category !=', '')
            ->findAll();
    }

    /**
     * Get statistics for policies.
     */
    public function getStatistics(int $schoolId): array
    {
        $total = $this->where('school_id', $schoolId)->countAllResults();

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
