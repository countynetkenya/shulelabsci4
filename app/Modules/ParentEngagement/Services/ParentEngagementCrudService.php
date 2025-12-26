<?php

namespace Modules\ParentEngagement\Services;

use Modules\Foundation\Services\AuditService;
use Modules\ParentEngagement\Models\SurveyModel;

/**
 * ParentEngagementCrudService - Business logic for parent engagement CRUD.
 *
 * All queries are tenant-scoped by school_id.
 * Integrates with AuditService for logging critical actions.
 */
class ParentEngagementCrudService
{
    protected SurveyModel $model;

    protected ?AuditService $auditService = null;

    public function __construct(?AuditService $auditService = null)
    {
        $this->model = new SurveyModel();

        // Try to inject AuditService
        try {
            $this->auditService = $auditService ?? new AuditService();
        } catch (\Throwable $e) {
            // AuditService not available, continue without it
            log_message('debug', 'AuditService not available: ' . $e->getMessage());
        }
    }

    /**
     * Get all surveys for a school.
     */
    public function getAll(int $schoolId, array $filters = []): array
    {
        $builder = $this->model->where('school_id', $schoolId);

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('title', $filters['search'])
                ->orLike('description', $filters['search'])
                ->groupEnd();
        }

        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        if (!empty($filters['survey_type'])) {
            $builder->where('survey_type', $filters['survey_type']);
        }

        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Get a single survey by ID (scoped to school).
     */
    public function getById(int $id, int $schoolId): ?array
    {
        $survey = $this->model
            ->where('school_id', $schoolId)
            ->where('id', $id)
            ->first();

        return $survey ?: null;
    }

    /**
     * Create a new survey.
     */
    public function create(array $data): int|false
    {
        // Set defaults
        if (!isset($data['status'])) {
            $data['status'] = 'draft';
        }
        if (!isset($data['is_anonymous'])) {
            $data['is_anonymous'] = 0;
        }
        if (!isset($data['response_count'])) {
            $data['response_count'] = 0;
        }

        $result = $this->model->insert($data);

        if ($result && $this->auditService) {
            try {
                $this->auditService->recordEvent(
                    'parent_engagement.survey.created',
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
     * Update an existing survey.
     */
    public function update(int $id, array $data): bool
    {
        $result = $this->model->update($id, $data);

        if ($result && $this->auditService) {
            try {
                $this->auditService->recordEvent(
                    'parent_engagement.survey.updated',
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
     * Delete a survey.
     */
    public function delete(int $id): bool
    {
        if ($this->auditService) {
            try {
                $this->auditService->recordEvent(
                    'parent_engagement.survey.deleted',
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
