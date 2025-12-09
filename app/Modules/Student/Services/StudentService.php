<?php

namespace App\Modules\Student\Services;

use App\Modules\Student\Models\StudentModel;
use Modules\Foundation\Services\AuditService;

/**
 * StudentService - Business logic for student management
 * 
 * All queries are tenant-scoped by school_id.
 * Integrates with AuditService for logging critical actions.
 */
class StudentService
{
    protected StudentModel $model;
    protected ?AuditService $auditService = null;

    public function __construct(?AuditService $auditService = null)
    {
        $this->model = new StudentModel();
        
        // Try to inject AuditService
        try {
            $this->auditService = $auditService ?? new AuditService();
        } catch (\Throwable $e) {
            // AuditService not available, continue without it
            log_message('debug', 'AuditService not available: ' . $e->getMessage());
        }
    }

    /**
     * Get all students for a school
     */
    public function getAll(int $schoolId, array $filters = []): array
    {
        return $this->model->getStudentsBySchool($schoolId, $filters);
    }

    /**
     * Get a single student by ID (scoped to school)
     */
    public function getById(int $id, int $schoolId): ?array
    {
        return $this->model->getStudentById($id, $schoolId);
    }

    /**
     * Create a new student
     */
    public function create(array $data): int|false
    {
        // Set default status if not provided
        if (!isset($data['status'])) {
            $data['status'] = 'active';
        }

        $result = $this->model->insert($data);

        if ($result && $this->auditService) {
            try {
                $this->auditService->recordEvent(
                    'student.created',
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
     * Update an existing student
     */
    public function update(int $id, array $data, int $schoolId): bool
    {
        // Get before state for audit
        $before = $this->getById($id, $schoolId);
        
        if (!$before) {
            return false;
        }

        $result = $this->model
            ->where('school_id', $schoolId)
            ->update($id, $data);

        if ($result && $this->auditService) {
            try {
                $this->auditService->recordEvent(
                    'student.updated',
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
     * Delete a student
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
                    'student.deleted',
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
     * Get active students count
     */
    public function getActiveCount(int $schoolId): int
    {
        return $this->model->getActiveCount($schoolId);
    }

    /**
     * Search students by name or admission number
     */
    public function search(int $schoolId, string $query): array
    {
        return $this->model->getStudentsBySchool($schoolId, ['search' => $query]);
    }

    /**
     * Get request metadata for audit logging
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
