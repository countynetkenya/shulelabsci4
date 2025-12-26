<?php

namespace App\Modules\Admissions\Services;

use App\Modules\Admissions\Models\AdmissionsApplicationModel;
use Modules\Foundation\Services\AuditService;

/**
 * AdmissionsCrudService - Business logic for admissions management.
 *
 * All queries are tenant-scoped by school_id.
 * Integrates with AuditService for logging critical actions.
 */
class AdmissionsCrudService
{
    protected AdmissionsApplicationModel $model;

    protected ?AuditService $auditService = null;

    public function __construct(?AuditService $auditService = null)
    {
        $this->model = new AdmissionsApplicationModel();

        // Try to inject AuditService
        try {
            $this->auditService = $auditService ?? new AuditService();
        } catch (\Throwable $e) {
            // AuditService not available, continue without it
            log_message('debug', 'AuditService not available: ' . $e->getMessage());
        }
    }

    /**
     * Get all applications for a school.
     */
    public function getAll(int $schoolId, array $filters = []): array
    {
        return $this->model->getApplicationsBySchool($schoolId, $filters);
    }

    /**
     * Get a single application by ID (scoped to school).
     */
    public function getById(int $id, int $schoolId): ?array
    {
        $application = $this->model
            ->where('school_id', $schoolId)
            ->where('id', $id)
            ->first();

        return $application ?: null;
    }

    /**
     * Create a new application.
     */
    public function create(array $data): int|false
    {
        // Auto-generate application number if not provided
        if (empty($data['application_number']) && !empty($data['school_id']) && !empty($data['academic_year'])) {
            $data['application_number'] = $this->model->generateApplicationNumber(
                $data['school_id'],
                $data['academic_year']
            );
        }

        // Set default status if not provided
        if (!isset($data['status'])) {
            $data['status'] = 'submitted';
        }

        $id = $this->model->insert($data);

        if ($id && $this->auditService) {
            try {
                $this->auditService->log(
                    'admissions',
                    'create',
                    $id,
                    $data,
                    'Application created: ' . ($data['application_number'] ?? $id)
                );
            } catch (\Throwable $e) {
                log_message('error', 'Audit log failed: ' . $e->getMessage());
            }
        }

        return $id;
    }

    /**
     * Update an existing application.
     */
    public function update(int $id, array $data, int $schoolId): bool
    {
        // Ensure application belongs to school
        $existing = $this->getById($id, $schoolId);
        if (!$existing) {
            return false;
        }

        $success = $this->model->update($id, $data);

        if ($success && $this->auditService) {
            try {
                $this->auditService->log(
                    'admissions',
                    'update',
                    $id,
                    $data,
                    'Application updated: ' . ($existing['application_number'] ?? $id)
                );
            } catch (\Throwable $e) {
                log_message('error', 'Audit log failed: ' . $e->getMessage());
            }
        }

        return $success;
    }

    /**
     * Delete an application.
     */
    public function delete(int $id, int $schoolId): bool
    {
        // Ensure application belongs to school
        $existing = $this->getById($id, $schoolId);
        if (!$existing) {
            return false;
        }

        $success = $this->model->delete($id);

        if ($success && $this->auditService) {
            try {
                $this->auditService->log(
                    'admissions',
                    'delete',
                    $id,
                    $existing,
                    'Application deleted: ' . ($existing['application_number'] ?? $id)
                );
            } catch (\Throwable $e) {
                log_message('error', 'Audit log failed: ' . $e->getMessage());
            }
        }

        return $success;
    }

    /**
     * Get application statistics.
     */
    public function getStatistics(int $schoolId, ?string $academicYear = null): array
    {
        return $this->model->getStatistics($schoolId, $academicYear);
    }
}
