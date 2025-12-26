<?php

namespace Modules\Security\Services;

use App\Modules\Security\Models\LoginAttemptModel;
use Modules\Foundation\Services\AuditService;

/**
 * SecurityService - Business logic for security management.
 *
 * Handles security logs, access attempts, and monitoring.
 * Integrates with AuditService for logging critical actions.
 */
class SecurityService
{
    protected LoginAttemptModel $model;

    protected ?AuditService $auditService = null;

    public function __construct(?AuditService $auditService = null)
    {
        $this->model = new LoginAttemptModel();

        // Try to inject AuditService
        try {
            $this->auditService = $auditService ?? new AuditService();
        } catch (\Throwable $e) {
            // AuditService not available, continue without it
            log_message('debug', 'AuditService not available: ' . $e->getMessage());
        }
    }

    /**
     * Get all access logs with filters.
     */
    public function getAll(array $filters = []): array
    {
        $builder = $this->model;

        if (!empty($filters['search'])) {
            $builder = $builder->groupStart()
                ->like('identifier', $filters['search'])
                ->orLike('ip_address', $filters['search'])
                ->groupEnd();
        }

        if (isset($filters['was_successful'])) {
            $builder = $builder->where('was_successful', $filters['was_successful']);
        }

        if (!empty($filters['attempt_type'])) {
            $builder = $builder->where('attempt_type', $filters['attempt_type']);
        }

        if (!empty($filters['date_from'])) {
            $builder = $builder->where('created_at >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder = $builder->where('created_at <=', $filters['date_to']);
        }

        return $builder->orderBy('created_at', 'DESC')->findAll(100);
    }

    /**
     * Get a single access log by ID.
     */
    public function getById(int $id): ?array
    {
        $log = $this->model->find($id);
        return $log ?: null;
    }

    /**
     * Create a new access log (manual entry).
     */
    public function create(array $data): int|false
    {
        $result = $this->model->insert($data);

        if ($result && $this->auditService) {
            try {
                $this->auditService->recordEvent(
                    'security.log.created',
                    'create',
                    [
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
     * Delete an access log.
     */
    public function delete(int $id): bool
    {
        if ($this->auditService) {
            try {
                $this->auditService->recordEvent(
                    'security.log.deleted',
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
     * Get security statistics.
     */
    public function getStatistics(int $days = 7): array
    {
        $since = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $total = $this->model->where('created_at >=', $since)->countAllResults(false);
        $successful = $this->model->where('was_successful', 1)->countAllResults(false);
        $failed = $this->model->where('was_successful', 0)->countAllResults();

        return [
            'total_attempts' => $total,
            'successful_attempts' => $successful,
            'failed_attempts' => $failed,
            'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Get request metadata for auditing.
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
