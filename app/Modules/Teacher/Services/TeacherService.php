<?php

namespace App\Modules\Teacher\Services;

use App\Modules\Teacher\Models\TeacherModel;
use Modules\Foundation\Services\AuditService;

/**
 * TeacherService - Business logic for teacher management.
 */
class TeacherService
{
    protected TeacherModel $model;

    protected ?AuditService $auditService = null;

    public function __construct(?AuditService $auditService = null)
    {
        $this->model = new TeacherModel();

        try {
            $this->auditService = $auditService ?? new AuditService();
        } catch (\Throwable $e) {
            log_message('debug', 'AuditService not available: ' . $e->getMessage());
        }
    }

    public function getAll(int $schoolId, array $filters = []): array
    {
        return $this->model->getTeachersBySchool($schoolId, $filters);
    }

    public function getById(int $id, int $schoolId): ?array
    {
        return $this->model->getTeacherById($id, $schoolId);
    }

    public function create(array $data): int|false
    {
        if (!isset($data['status'])) {
            $data['status'] = 'active';
        }

        $result = $this->model->insert($data);

        if ($result && $this->auditService) {
            try {
                $this->auditService->recordEvent(
                    'teacher.created',
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

    public function update(int $id, array $data, int $schoolId): bool
    {
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
                    'teacher.updated',
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

    public function delete(int $id, int $schoolId): bool
    {
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
                    'teacher.deleted',
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

    public function getActiveCount(int $schoolId): int
    {
        return $this->model->getActiveCount($schoolId);
    }

    public function search(int $schoolId, string $query): array
    {
        return $this->model->getTeachersBySchool($schoolId, ['search' => $query]);
    }

    public function getDepartments(int $schoolId): array
    {
        return $this->model->getDepartments($schoolId);
    }

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
