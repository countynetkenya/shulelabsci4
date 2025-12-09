<?php

namespace App\Modules\Monitoring\Services;

use App\Modules\Monitoring\Models\MetricModel;

/**
 * MonitoringCrudService - CRUD operations for metrics management
 */
class MonitoringCrudService
{
    protected MetricModel $model;

    public function __construct()
    {
        $this->model = new MetricModel();
    }

    /**
     * Get all metrics for a school
     */
    public function getAll(int $schoolId, array $filters = []): array
    {
        return $this->model->getBySchool($schoolId, $filters, 50, 0);
    }

    /**
     * Get a single metric by ID (scoped to school)
     */
    public function getById(int $id, int $schoolId): ?array
    {
        $metric = $this->model
            ->where('school_id', $schoolId)
            ->where('id', $id)
            ->first();
        
        return $metric ?: null;
    }

    /**
     * Create a new metric
     */
    public function create(array $data): int|false
    {
        // Ensure recorded_at is set
        if (!isset($data['recorded_at'])) {
            $data['recorded_at'] = date('Y-m-d H:i:s');
        }

        // Parse labels if provided as JSON string
        if (isset($data['labels']) && is_string($data['labels'])) {
            $data['labels'] = json_decode($data['labels'], true) ?? [];
        }

        if ($this->model->insert($data)) {
            return (int) $this->model->getInsertID();
        }

        return false;
    }

    /**
     * Update a metric
     */
    public function update(int $id, array $data): bool
    {
        // Parse labels if provided as JSON string
        if (isset($data['labels']) && is_string($data['labels'])) {
            $data['labels'] = json_decode($data['labels'], true) ?? [];
        }

        return $this->model->update($id, $data);
    }

    /**
     * Delete a metric
     */
    public function delete(int $id): bool
    {
        return $this->model->delete($id);
    }

    /**
     * Get available metric names
     */
    public function getMetricNames(int $schoolId): array
    {
        return $this->model->getMetricNames($schoolId);
    }

    /**
     * Get metric types
     */
    public function getMetricTypes(): array
    {
        return ['counter', 'gauge', 'histogram', 'summary'];
    }
}
