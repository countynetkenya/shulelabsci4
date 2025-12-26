<?php

namespace App\Modules\Integrations\Services;

use Modules\Integrations\Models\IntegrationModel;

/**
 * IntegrationsCrudService - CRUD operations for integrations management.
 */
class IntegrationsCrudService
{
    protected IntegrationModel $model;

    public function __construct()
    {
        $this->model = new IntegrationModel();
    }

    /**
     * Get all integrations (optionally filtered by tenant).
     */
    public function getAll(?string $tenantId = null, array $filters = []): array
    {
        $builder = $this->model;

        if ($tenantId !== null) {
            $builder = $builder->where('tenant_id', $tenantId);
        }

        if (!empty($filters['type'])) {
            $builder = $builder->where('type', $filters['type']);
        }
        if (!empty($filters['is_active'])) {
            $builder = $builder->where('is_active', $filters['is_active']);
        }

        return $builder->orderBy('name', 'ASC')->findAll();
    }

    /**
     * Get a single integration by ID.
     */
    public function getById(int $id, ?string $tenantId = null): ?array
    {
        $builder = $this->model->where('id', $id);

        if ($tenantId !== null) {
            $builder = $builder->where('tenant_id', $tenantId);
        }

        $integration = $builder->first();

        return $integration ?: null;
    }

    /**
     * Create a new integration.
     */
    public function create(array $data): int|false
    {
        // Parse config_json if provided as string
        if (isset($data['config_json']) && is_string($data['config_json'])) {
            $decoded = json_decode($data['config_json'], true);
            if ($decoded !== null) {
                $data['config_json'] = json_encode($decoded);
            }
        }

        if ($this->model->insert($data)) {
            return (int) $this->model->getInsertID();
        }

        return false;
    }

    /**
     * Update an integration.
     */
    public function update(int $id, array $data): bool
    {
        // Parse config_json if provided as string
        if (isset($data['config_json']) && is_string($data['config_json'])) {
            $decoded = json_decode($data['config_json'], true);
            if ($decoded !== null) {
                $data['config_json'] = json_encode($decoded);
            }
        }

        return $this->model->update($id, $data);
    }

    /**
     * Delete an integration (soft delete).
     */
    public function delete(int $id): bool
    {
        return $this->model->delete($id);
    }

    /**
     * Get integration types.
     */
    public function getIntegrationTypes(): array
    {
        return [
            'payment' => 'Payment Gateway',
            'communication' => 'Communication (SMS/Email)',
            'storage' => 'Cloud Storage',
            'lms' => 'Learning Management System',
            'analytics' => 'Analytics & Tracking',
            'other' => 'Other',
        ];
    }

    /**
     * Get validation errors from model.
     */
    public function getErrors(): array
    {
        return $this->model->errors();
    }
}
