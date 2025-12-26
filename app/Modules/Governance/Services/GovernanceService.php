<?php

namespace App\Modules\Governance\Services;

use App\Modules\Governance\Models\GovernancePolicyModel;
use Modules\Foundation\Services\AuditService;

/**
 * GovernanceService - Business logic for governance policy management.
 *
 * All queries are tenant-scoped by school_id.
 * Integrates with AuditService for logging critical actions.
 */
class GovernanceService
{
    protected GovernancePolicyModel $model;

    protected ?AuditService $auditService = null;

    public function __construct(?AuditService $auditService = null)
    {
        $this->model = new GovernancePolicyModel();

        // Try to inject AuditService
        try {
            $this->auditService = $auditService ?? new AuditService();
        } catch (\Throwable $e) {
            // AuditService not available, continue without it
            log_message('debug', 'AuditService not available: ' . $e->getMessage());
        }
    }

    /**
     * Get all policies for a school.
     */
    public function getAll(int $schoolId, array $filters = []): array
    {
        return $this->model->getPoliciesBySchool($schoolId, $filters);
    }

    /**
     * Get a single policy by ID (scoped to school).
     */
    public function getById(int $id, int $schoolId): ?array
    {
        $policy = $this->model
            ->where('school_id', $schoolId)
            ->where('id', $id)
            ->first();

        return $policy ?: null;
    }

    /**
     * Create a new policy.
     */
    public function create(array $data): int|false
    {
        // Auto-generate policy number if not provided
        if (empty($data['policy_number']) && !empty($data['school_id']) && !empty($data['category'])) {
            $data['policy_number'] = $this->model->generatePolicyNumber(
                $data['school_id'],
                $data['category']
            );
        }

        // Set defaults
        if (!isset($data['status'])) {
            $data['status'] = 'draft';
        }
        if (!isset($data['version'])) {
            $data['version'] = '1.0';
        }

        $id = $this->model->insert($data);

        if ($id && $this->auditService) {
            try {
                $this->auditService->log(
                    'governance',
                    'create',
                    $id,
                    $data,
                    'Policy created: ' . ($data['title'] ?? $id)
                );
            } catch (\Throwable $e) {
                log_message('error', 'Audit log failed: ' . $e->getMessage());
            }
        }

        return $id;
    }

    /**
     * Update an existing policy.
     */
    public function update(int $id, array $data, int $schoolId): bool
    {
        // Ensure policy belongs to school
        $existing = $this->getById($id, $schoolId);
        if (!$existing) {
            return false;
        }

        $success = $this->model->update($id, $data);

        if ($success && $this->auditService) {
            try {
                $this->auditService->log(
                    'governance',
                    'update',
                    $id,
                    $data,
                    'Policy updated: ' . ($existing['title'] ?? $id)
                );
            } catch (\Throwable $e) {
                log_message('error', 'Audit log failed: ' . $e->getMessage());
            }
        }

        return $success;
    }

    /**
     * Delete a policy.
     */
    public function delete(int $id, int $schoolId): bool
    {
        // Ensure policy belongs to school
        $existing = $this->getById($id, $schoolId);
        if (!$existing) {
            return false;
        }

        $success = $this->model->delete($id);

        if ($success && $this->auditService) {
            try {
                $this->auditService->log(
                    'governance',
                    'delete',
                    $id,
                    $existing,
                    'Policy deleted: ' . ($existing['title'] ?? $id)
                );
            } catch (\Throwable $e) {
                log_message('error', 'Audit log failed: ' . $e->getMessage());
            }
        }

        return $success;
    }

    /**
     * Get policy categories.
     */
    public function getCategories(int $schoolId): array
    {
        return $this->model->getCategories($schoolId);
    }

    /**
     * Get policy statistics.
     */
    public function getStatistics(int $schoolId): array
    {
        return $this->model->getStatistics($schoolId);
    }
}
