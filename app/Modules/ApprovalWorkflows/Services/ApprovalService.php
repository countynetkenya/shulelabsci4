<?php

namespace App\Modules\ApprovalWorkflows\Services;

use App\Modules\ApprovalWorkflows\Models\WorkflowModel;
use Modules\Foundation\Services\AuditService;

/**
 * ApprovalService - Business logic for approval workflow management
 * 
 * All queries are tenant-scoped by school_id.
 * Integrates with AuditService for logging critical actions.
 */
class ApprovalService
{
    protected WorkflowModel $model;
    protected ?AuditService $auditService = null;

    public function __construct(?AuditService $auditService = null)
    {
        $this->model = new WorkflowModel();
        
        // Try to inject AuditService
        try {
            $this->auditService = $auditService ?? new AuditService();
        } catch (\Throwable $e) {
            // AuditService not available, continue without it
            log_message('debug', 'AuditService not available: ' . $e->getMessage());
        }
    }

    /**
     * Get all approval requests for a school
     * 
     * @param int $schoolId
     * @param array $filters Optional filters
     * @return array
     */
    public function getAll(int $schoolId, array $filters = []): array
    {
        return $this->model->getRequestsBySchool($schoolId, $filters);
    }

    /**
     * Get a single approval request by ID (scoped to school)
     * 
     * @param int $id
     * @param int $schoolId
     * @return array|null
     */
    public function getById(int $id, int $schoolId): ?array
    {
        $request = $this->model
            ->where('school_id', $schoolId)
            ->where('id', $id)
            ->first();
        
        return $request ?: null;
    }

    /**
     * Create a new approval request
     * 
     * @param array $data
     * @return int|false Request ID or false on failure
     */
    public function create(array $data): int|false
    {
        // Ensure requested_at is set
        if (!isset($data['requested_at'])) {
            $data['requested_at'] = date('Y-m-d H:i:s');
        }

        // Ensure requested_by is set
        if (!isset($data['requested_by'])) {
            $data['requested_by'] = session()->get('user_id') ?? 1;
        }

        // Default status
        if (!isset($data['status'])) {
            $data['status'] = 'pending';
        }

        // Default priority
        if (!isset($data['priority'])) {
            $data['priority'] = 'normal';
        }

        // Encode request_data as JSON if it's an array
        if (isset($data['request_data']) && is_array($data['request_data'])) {
            $data['request_data'] = json_encode($data['request_data']);
        }

        $result = $this->model->insert($data);

        if ($result && $this->auditService) {
            try {
                $this->auditService->recordEvent(
                    'approval.request.created',
                    'create',
                    [
                        'school_id' => $data['school_id'] ?? null,
                        'actor_id'  => session()->get('user_id'),
                    ],
                    null,
                    $data,
                    $this->getRequestMetadata()
                );
            } catch (\Throwable $e) {
                log_message('warning', 'Audit log failed: ' . $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Update an existing approval request
     * 
     * @param int $id
     * @param array $data
     * @param int $schoolId
     * @return bool
     */
    public function update(int $id, array $data, int $schoolId): bool
    {
        // Get before state for audit
        $before = $this->getById($id, $schoolId);
        
        if (!$before) {
            return false;
        }

        // Encode request_data as JSON if it's an array
        if (isset($data['request_data']) && is_array($data['request_data'])) {
            $data['request_data'] = json_encode($data['request_data']);
        }

        $result = $this->model
            ->where('school_id', $schoolId)
            ->update($id, $data);

        if ($result && $this->auditService) {
            try {
                $this->auditService->recordEvent(
                    'approval.request.updated',
                    'update',
                    [
                        'school_id' => $schoolId,
                        'actor_id'  => session()->get('user_id'),
                    ],
                    $before,
                    array_merge($before, $data),
                    $this->getRequestMetadata()
                );
            } catch (\Throwable $e) {
                log_message('warning', 'Audit log failed: ' . $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Delete an approval request
     * 
     * @param int $id
     * @param int $schoolId
     * @return bool
     */
    public function delete(int $id, int $schoolId): bool
    {
        // Get before state for audit
        $before = $this->getById($id, $schoolId);
        
        if (!$before) {
            return false;
        }

        $result = $this->model
            ->where('school_id', $schoolId)
            ->delete($id);

        if ($result && $this->auditService) {
            try {
                $this->auditService->recordEvent(
                    'approval.request.deleted',
                    'delete',
                    [
                        'school_id' => $schoolId,
                        'actor_id'  => session()->get('user_id'),
                    ],
                    $before,
                    null,
                    $this->getRequestMetadata()
                );
            } catch (\Throwable $e) {
                log_message('warning', 'Audit log failed: ' . $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Get available statuses
     * 
     * @param int $schoolId
     * @return array
     */
    public function getStatuses(int $schoolId): array
    {
        return $this->model->getStatuses($schoolId);
    }

    /**
     * Get approval request summary
     * 
     * @param int $schoolId
     * @return array
     */
    public function getSummary(int $schoolId): array
    {
        return $this->model->getRequestSummary($schoolId);
    }

    /**
     * Get requests by workflow
     * 
     * @param int $workflowId
     * @param int $schoolId
     * @return array
     */
    public function getByWorkflow(int $workflowId, int $schoolId): array
    {
        return $this->model->getRequestsByWorkflow($workflowId, $schoolId);
    }

    /**
     * Get pending requests for a user
     * 
     * @param int $userId
     * @param int $schoolId
     * @return array
     */
    public function getPendingForUser(int $userId, int $schoolId): array
    {
        return $this->model->getPendingForUser($userId, $schoolId);
    }

    /**
     * Approve a request
     * 
     * @param int $id
     * @param int $schoolId
     * @param string $comments
     * @return bool
     */
    public function approve(int $id, int $schoolId, string $comments = ''): bool
    {
        return $this->update($id, [
            'status'       => 'approved',
            'completed_at' => date('Y-m-d H:i:s'),
        ], $schoolId);
    }

    /**
     * Reject a request
     * 
     * @param int $id
     * @param int $schoolId
     * @param string $comments
     * @return bool
     */
    public function reject(int $id, int $schoolId, string $comments = ''): bool
    {
        return $this->update($id, [
            'status'       => 'rejected',
            'completed_at' => date('Y-m-d H:i:s'),
        ], $schoolId);
    }

    /**
     * Get request metadata for audit logging
     * 
     * @return array
     */
    protected function getRequestMetadata(): array
    {
        $request = service('request');
        
        return [
            'ip'          => $request->getIPAddress(),
            'user_agent'  => $request->getUserAgent()->getAgentString(),
            'request_uri' => current_url(),
        ];
    }
}
