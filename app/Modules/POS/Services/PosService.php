<?php

namespace App\Modules\POS\Services;

use App\Modules\POS\Models\PosProductModel;
use Modules\Foundation\Services\AuditService;

/**
 * PosService - Business logic for POS product management
 * 
 * All queries are tenant-scoped by school_id.
 * Integrates with AuditService for logging critical actions.
 */
class PosService
{
    protected PosProductModel $model;
    protected ?AuditService $auditService = null;

    public function __construct(?AuditService $auditService = null)
    {
        $this->model = new PosProductModel();
        
        // Try to inject AuditService
        try {
            $this->auditService = $auditService ?? new AuditService();
        } catch (\Throwable $e) {
            // AuditService not available, continue without it
            log_message('debug', 'AuditService not available: ' . $e->getMessage());
        }
    }

    /**
     * Get all products for a school
     */
    public function getAll(int $schoolId, array $filters = []): array
    {
        $builder = $this->model->where('school_id', $schoolId);

        // Apply filters
        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('name', $filters['search'])
                ->orLike('description', $filters['search'])
                ->orLike('sku', $filters['search'])
                ->groupEnd();
        }

        if (!empty($filters['category'])) {
            $builder->where('category', $filters['category']);
        }

        if (isset($filters['is_active'])) {
            $builder->where('is_active', $filters['is_active']);
        }

        return $builder->findAll();
    }

    /**
     * Get a single product by ID (scoped to school)
     */
    public function getById(int $id, int $schoolId): ?array
    {
        $product = $this->model
            ->where('school_id', $schoolId)
            ->where('id', $id)
            ->first();
        
        return $product ?: null;
    }

    /**
     * Create a new product
     */
    public function create(array $data): int|false
    {
        $result = $this->model->insert($data);

        if ($result && $this->auditService) {
            try {
                $this->auditService->recordEvent(
                    'pos.product.created',
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
     * Update an existing product
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
                    'pos.product.updated',
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
     * Delete a product
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
                    'pos.product.deleted',
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
     * Get all categories for a school
     */
    public function getCategories(int $schoolId): array
    {
        return $this->model
            ->select('DISTINCT category as category', false)
            ->where('school_id', $schoolId)
            ->whereNotNull('category')
            ->where('category !=', '')
            ->orderBy('category', 'ASC')
            ->findAll();
    }

    /**
     * Update stock quantity
     */
    public function updateStock(int $id, int $quantity, int $schoolId): bool
    {
        $product = $this->getById($id, $schoolId);
        
        if (!$product) {
            return false;
        }

        return $this->model
            ->where('school_id', $schoolId)
            ->update($id, ['stock' => $quantity]);
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
