<?php

namespace App\Modules\Analytics\Services;

use App\Modules\Analytics\Models\AnalyticsDashboardModel;
use Modules\Foundation\Services\AuditService;

/**
 * AnalyticsCrudService - Business logic for analytics dashboard management.
 *
 * All queries are tenant-scoped by school_id.
 * Integrates with AuditService for logging critical actions.
 */
class AnalyticsCrudService
{
    protected AnalyticsDashboardModel $model;

    protected ?AuditService $auditService = null;

    public function __construct(?AuditService $auditService = null)
    {
        $this->model = new AnalyticsDashboardModel();

        // Try to inject AuditService
        try {
            $this->auditService = $auditService ?? new AuditService();
        } catch (\Throwable $e) {
            // AuditService not available, continue without it
            log_message('debug', 'AuditService not available: ' . $e->getMessage());
        }
    }

    /**
     * Get all dashboards for a school.
     */
    public function getAll(int $schoolId, ?int $userId = null): array
    {
        return $this->model->getDashboardsBySchool($schoolId, $userId);
    }

    /**
     * Get a single dashboard by ID (scoped to school).
     */
    public function getById(int $id, int $schoolId): ?array
    {
        $dashboard = $this->model
            ->where('school_id', $schoolId)
            ->where('id', $id)
            ->first();

        return $dashboard ?: null;
    }

    /**
     * Create a new dashboard.
     */
    public function create(array $data): int|false
    {
        // Set defaults
        if (!isset($data['is_default'])) {
            $data['is_default'] = 0;
        }
        if (!isset($data['is_shared'])) {
            $data['is_shared'] = 0;
        }
        if (!isset($data['layout'])) {
            $data['layout'] = json_encode([]);
        } elseif (is_array($data['layout'])) {
            $data['layout'] = json_encode($data['layout']);
        }

        $id = $this->model->insert($data);

        if ($id && $this->auditService) {
            try {
                $this->auditService->log(
                    'analytics',
                    'create',
                    $id,
                    $data,
                    'Dashboard created: ' . ($data['name'] ?? $id)
                );
            } catch (\Throwable $e) {
                log_message('error', 'Audit log failed: ' . $e->getMessage());
            }
        }

        return $id;
    }

    /**
     * Update an existing dashboard.
     */
    public function update(int $id, array $data, int $schoolId): bool
    {
        // Ensure dashboard belongs to school
        $existing = $this->getById($id, $schoolId);
        if (!$existing) {
            return false;
        }

        // Handle layout encoding
        if (isset($data['layout']) && is_array($data['layout'])) {
            $data['layout'] = json_encode($data['layout']);
        }

        $success = $this->model->update($id, $data);

        if ($success && $this->auditService) {
            try {
                $this->auditService->log(
                    'analytics',
                    'update',
                    $id,
                    $data,
                    'Dashboard updated: ' . ($existing['name'] ?? $id)
                );
            } catch (\Throwable $e) {
                log_message('error', 'Audit log failed: ' . $e->getMessage());
            }
        }

        return $success;
    }

    /**
     * Delete a dashboard.
     */
    public function delete(int $id, int $schoolId): bool
    {
        // Ensure dashboard belongs to school
        $existing = $this->getById($id, $schoolId);
        if (!$existing) {
            return false;
        }

        $success = $this->model->delete($id);

        if ($success && $this->auditService) {
            try {
                $this->auditService->log(
                    'analytics',
                    'delete',
                    $id,
                    $existing,
                    'Dashboard deleted: ' . ($existing['name'] ?? $id)
                );
            } catch (\Throwable $e) {
                log_message('error', 'Audit log failed: ' . $e->getMessage());
            }
        }

        return $success;
    }
}
