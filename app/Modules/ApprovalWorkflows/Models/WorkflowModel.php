<?php

namespace App\Modules\ApprovalWorkflows\Models;

use CodeIgniter\Model;

/**
 * WorkflowModel - Handles approval workflow requests.
 *
 * Manages approval request records including:
 * - Workflow-based approvals
 * - Multi-level approval tracking
 * - Request status management
 *
 * All data is tenant-scoped by school_id.
 */
class WorkflowModel extends Model
{
    protected $table = 'approval_requests';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = false;

    protected $protectFields = true;

    protected $allowedFields = [
        'school_id',
        'workflow_id',
        'current_stage_id',
        'entity_type',
        'entity_id',
        'request_data',
        'status',
        'priority',
        'requested_by',
        'requested_at',
        'completed_at',
        'expires_at',
    ];

    // Dates
    protected $useTimestamps = true;

    protected $dateFormat = 'datetime';

    protected $createdField = 'created_at';

    protected $updatedField = false; // No updated_at field in this table

    protected $deletedField = false; // No soft deletes

    // Validation
    protected $validationRules = [
        'school_id'    => 'required|integer',
        'workflow_id'  => 'required|integer',
        'entity_type'  => 'required|max_length[100]',
        'entity_id'    => 'required|integer',
        'status'       => 'required|in_list[pending,in_progress,approved,rejected,cancelled,expired]',
        'priority'     => 'permit_empty|in_list[low,normal,high,urgent]',
        'requested_by' => 'required|integer',
        'requested_at' => 'required|valid_date',
    ];

    protected $validationMessages = [
        'school_id' => [
            'required' => 'School is required',
            'integer'  => 'Invalid school ID',
        ],
        'workflow_id' => [
            'required' => 'Workflow is required',
            'integer'  => 'Invalid workflow ID',
        ],
        'entity_type' => [
            'required'   => 'Entity type is required',
            'max_length' => 'Entity type must not exceed 100 characters',
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list'  => 'Invalid status value',
        ],
    ];

    protected $skipValidation = false;

    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;

    protected $beforeInsert = [];

    protected $afterInsert = [];

    protected $beforeUpdate = [];

    protected $afterUpdate = [];

    protected $beforeFind = [];

    protected $afterFind = [];

    protected $beforeDelete = [];

    protected $afterDelete = [];

    /**
     * Get all approval requests for a school.
     *
     * @param int $schoolId
     * @param array $filters Optional filters (search, status, priority)
     * @return array
     */
    public function getRequestsBySchool(int $schoolId, array $filters = []): array
    {
        $builder = $this->where('school_id', $schoolId);

        // Apply filters
        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('entity_type', $filters['search'])
                ->orLike('status', $filters['search'])
                ->groupEnd();
        }

        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $builder->where('priority', $filters['priority']);
        }

        return $builder->orderBy('requested_at', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get approval request statuses used in the school.
     *
     * @param int $schoolId
     * @return array Array of unique statuses
     */
    public function getStatuses(int $schoolId): array
    {
        $results = $this->select('DISTINCT status as request_status', false)
            ->where('school_id', $schoolId)
            ->whereNotNull('status')
            ->findAll();

        return array_column($results, 'request_status');
    }

    /**
     * Get approval request summary for a school.
     *
     * @param int $schoolId
     * @return array
     */
    public function getRequestSummary(int $schoolId): array
    {
        $result = $this->select('
                COUNT(*) as total_requests,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as in_progress_count,
                SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved_count,
                SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected_count
            ')
            ->where('school_id', $schoolId)
            ->get()
            ->getRowArray();

        return $result ?: [
            'total_requests'     => 0,
            'pending_count'      => 0,
            'in_progress_count'  => 0,
            'approved_count'     => 0,
            'rejected_count'     => 0,
        ];
    }

    /**
     * Get requests by workflow.
     *
     * @param int $workflowId
     * @param int $schoolId
     * @return array
     */
    public function getRequestsByWorkflow(int $workflowId, int $schoolId): array
    {
        return $this->where('workflow_id', $workflowId)
            ->where('school_id', $schoolId)
            ->orderBy('requested_at', 'DESC')
            ->findAll();
    }

    /**
     * Get pending requests for a user.
     *
     * @param int $userId
     * @param int $schoolId
     * @return array
     */
    public function getPendingForUser(int $userId, int $schoolId): array
    {
        return $this->where('requested_by', $userId)
            ->where('school_id', $schoolId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('requested_at', 'DESC')
            ->findAll();
    }
}
