<?php

namespace App\Modules\ApprovalWorkflows\Services;

use CodeIgniter\Database\ConnectionInterface;
use Config\Database;

/**
 * ApprovalWorkflowService - Handles maker-checker approval workflows.
 */
class ApprovalWorkflowService
{
    private $db;

    public function __construct(?ConnectionInterface $connection = null)
    {
        $this->db = $connection ?? Database::connect();
    }

    /**
     * Submit an item for approval.
     */
    public function submit(string $entityType, int $entityId, array $requestData, ?int $workflowId = null): int
    {
        $schoolId = session('school_id');

        // Find applicable workflow
        if (!$workflowId) {
            $workflow = $this->findWorkflow($entityType, $schoolId);
            if (!$workflow) {
                throw new \RuntimeException("No workflow configured for {$entityType}");
            }
            $workflowId = $workflow['id'];
        }

        // Get first stage
        $firstStage = $this->db->table('approval_stages')
            ->where('workflow_id', $workflowId)
            ->where('is_active', 1)
            ->orderBy('sequence', 'ASC')
            ->get()
            ->getRowArray();

        $this->db->table('approval_requests')->insert([
            'school_id' => $schoolId,
            'workflow_id' => $workflowId,
            'current_stage_id' => $firstStage ? $firstStage['id'] : null,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'request_data' => json_encode($requestData),
            'status' => 'pending',
            'requested_by' => session('user_id'),
            'requested_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insertID();
    }

    /**
     * Approve a request.
     */
    public function approve(int $requestId, ?string $comments = null): bool
    {
        return $this->processAction($requestId, 'approve', $comments);
    }

    /**
     * Reject a request.
     */
    public function reject(int $requestId, string $reason): bool
    {
        return $this->processAction($requestId, 'reject', $reason);
    }

    /**
     * Delegate approval to another user.
     */
    public function delegate(int $requestId, int $toUserId, ?string $comments = null): bool
    {
        $this->db->table('approval_actions')->insert([
            'request_id' => $requestId,
            'stage_id' => $this->getCurrentStageId($requestId),
            'action' => 'delegate',
            'comments' => $comments,
            'delegated_to' => $toUserId,
            'action_by' => session('user_id'),
            'action_at' => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    /**
     * Get pending approvals for a user.
     */
    public function getPendingForUser(int $userId): array
    {
        // Get user's roles
        $userRoles = $this->db->table('user_roles')
            ->select('role_id')
            ->where('user_id', $userId)
            ->get()
            ->getResultArray();

        $roleIds = array_column($userRoles, 'role_id');

        // Get requests where user is an approver
        return $this->db->table('approval_requests ar')
            ->select('ar.*, aw.name as workflow_name, ast.name as stage_name')
            ->join('approval_workflows aw', 'aw.id = ar.workflow_id')
            ->join('approval_stages ast', 'ast.id = ar.current_stage_id', 'left')
            ->whereIn('ar.status', ['pending', 'in_progress'])
            ->groupStart()
            ->whereIn('ast.approver_ids', array_map(fn ($id) => json_encode([$id]), [$userId]), false)
            ->orWhereIn('ast.approver_ids', array_map(fn ($id) => json_encode([$id]), $roleIds), false)
            ->groupEnd()
            ->get()
            ->getResultArray();
    }

    /**
     * Get approval history for a request.
     */
    public function getHistory(int $requestId): array
    {
        return $this->db->table('approval_actions aa')
            ->select('aa.*, u.first_name, u.last_name, ast.name as stage_name')
            ->join('users u', 'u.id = aa.action_by')
            ->join('approval_stages ast', 'ast.id = aa.stage_id', 'left')
            ->where('aa.request_id', $requestId)
            ->orderBy('aa.action_at', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Check if a request is approved.
     */
    public function isApproved(int $requestId): bool
    {
        $request = $this->db->table('approval_requests')
            ->select('status')
            ->where('id', $requestId)
            ->get()
            ->getRowArray();

        return $request && $request['status'] === 'approved';
    }

    /**
     * Process an approval action.
     */
    private function processAction(int $requestId, string $action, ?string $comments): bool
    {
        $request = $this->db->table('approval_requests')
            ->where('id', $requestId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->get()
            ->getRowArray();

        if (!$request) {
            return false;
        }

        $this->db->transStart();

        // Record action
        $this->db->table('approval_actions')->insert([
            'request_id' => $requestId,
            'stage_id' => $request['current_stage_id'],
            'action' => $action,
            'comments' => $comments,
            'action_by' => session('user_id'),
            'action_at' => date('Y-m-d H:i:s'),
        ]);

        if ($action === 'reject') {
            // Reject the entire request
            $this->db->table('approval_requests')
                ->where('id', $requestId)
                ->update([
                    'status' => 'rejected',
                    'completed_at' => date('Y-m-d H:i:s'),
                ]);
        } else {
            // Check if we need more approvals for this stage
            $stageApprovals = $this->countStageApprovals($requestId, $request['current_stage_id']);
            $stage = $this->db->table('approval_stages')
                ->where('id', $request['current_stage_id'])
                ->get()
                ->getRowArray();

            $needsMore = $stage && $stageApprovals < $stage['min_approvers'];

            if (!$needsMore) {
                // Move to next stage or complete
                $nextStage = $this->getNextStage($request['workflow_id'], $request['current_stage_id']);

                if ($nextStage) {
                    $this->db->table('approval_requests')
                        ->where('id', $requestId)
                        ->update([
                            'current_stage_id' => $nextStage['id'],
                            'status' => 'in_progress',
                        ]);
                } else {
                    // All stages complete
                    $this->db->table('approval_requests')
                        ->where('id', $requestId)
                        ->update([
                            'status' => 'approved',
                            'completed_at' => date('Y-m-d H:i:s'),
                        ]);
                }
            }
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Find applicable workflow.
     */
    private function findWorkflow(string $entityType, int $schoolId): ?array
    {
        return $this->db->table('approval_workflows')
            ->where('entity_type', $entityType)
            ->where('is_active', 1)
            ->groupStart()
            ->where('school_id', $schoolId)
            ->orWhere('school_id IS NULL')
            ->groupEnd()
            ->orderBy('school_id', 'DESC')
            ->get()
            ->getRowArray();
    }

    /**
     * Get next stage in workflow.
     */
    private function getNextStage(int $workflowId, int $currentStageId): ?array
    {
        $currentStage = $this->db->table('approval_stages')
            ->where('id', $currentStageId)
            ->get()
            ->getRowArray();

        if (!$currentStage) {
            return null;
        }

        return $this->db->table('approval_stages')
            ->where('workflow_id', $workflowId)
            ->where('sequence >', $currentStage['sequence'])
            ->where('is_active', 1)
            ->orderBy('sequence', 'ASC')
            ->get()
            ->getRowArray();
    }

    /**
     * Count approvals for a stage.
     */
    private function countStageApprovals(int $requestId, int $stageId): int
    {
        return $this->db->table('approval_actions')
            ->where('request_id', $requestId)
            ->where('stage_id', $stageId)
            ->where('action', 'approve')
            ->countAllResults();
    }

    /**
     * Get current stage ID.
     */
    private function getCurrentStageId(int $requestId): ?int
    {
        $request = $this->db->table('approval_requests')
            ->select('current_stage_id')
            ->where('id', $requestId)
            ->get()
            ->getRowArray();

        return $request ? (int) $request['current_stage_id'] : null;
    }
}
