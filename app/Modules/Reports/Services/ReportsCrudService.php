<?php

namespace Modules\Reports\Services;

use Modules\Reports\Models\ReportModel;
use Modules\Foundation\Services\AuditService;

/**
 * ReportsService - Business logic for report management
 * 
 * All queries are tenant-scoped by school_id.
 * Integrates with AuditService for logging critical actions.
 */
class ReportsService
{
    protected ReportModel $model;
    protected ?AuditService $auditService = null;

    public function __construct(?AuditService $auditService = null)
    {
        $this->model = new ReportModel();
        
        // Try to inject AuditService
        try {
            $this->auditService = $auditService ?? new AuditService();
        } catch (\Throwable $e) {
            // AuditService not available, continue without it
            log_message('debug', 'AuditService not available: ' . $e->getMessage());
        }
    }

    /**
     * Get all reports for a school
     */
    public function getAll(int $schoolId, array $filters = []): array
    {
        return $this->model->getReportsBySchool($schoolId, $filters);
    }

    /**
     * Get a single report by ID (scoped to school)
     */
    public function getById(int $id, int $schoolId): ?array
    {
        $report = $this->model
            ->where('school_id', $schoolId)
            ->where('id', $id)
            ->first();
        
        return $report ?: null;
    }

    /**
     * Create a new report
     */
    public function create(array $data): int|false
    {
        $result = $this->model->insert($data);

        if ($result && $this->auditService) {
            try {
                $this->auditService->recordEvent(
                    'reports.created',
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
                log_message('error', 'Audit logging failed: ' . $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Update an existing report
     */
    public function update(int $id, array $data): bool
    {
        $result = $this->model->update($id, $data);

        if ($result && $this->auditService) {
            try {
                $this->auditService->recordEvent(
                    'reports.updated',
                    'update',
                    [
                        'school_id' => $data['school_id'] ?? null,
                        'actor_id'  => session()->get('user_id'),
                    ],
                    null,
                    $data,
                    $this->getRequestMetadata()
                );
            } catch (\Throwable $e) {
                log_message('error', 'Audit logging failed: ' . $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Delete a report
     */
    public function delete(int $id): bool
    {
        if ($this->auditService) {
            try {
                $this->auditService->recordEvent(
                    'reports.deleted',
                    'delete',
                    [
                        'actor_id' => session()->get('user_id'),
                    ],
                    null,
                    ['id' => $id],
                    $this->getRequestMetadata()
                );
            } catch (\Throwable $e) {
                log_message('error', 'Audit logging failed: ' . $e->getMessage());
            }
        }

        return $this->model->delete($id);
    }

    /**
     * Get available templates
     */
    public function getTemplates(): array
    {
        return $this->model->getTemplates();
    }

    /**
     * Get request metadata for auditing
     */
    protected function getRequestMetadata(): array
    {
        $request = service('request');
        return [
            'ip_address' => $request->getIPAddress(),
            'user_agent' => $request->getUserAgent()->__toString(),
        ];
    }
}
