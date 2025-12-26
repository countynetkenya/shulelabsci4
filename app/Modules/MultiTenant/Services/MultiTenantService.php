<?php

namespace App\Modules\MultiTenant\Services;

use App\Modules\MultiTenant\Models\TenantModel;
use Modules\Foundation\Services\AuditService;

/**
 * MultiTenantService - Business logic for tenant/school management.
 *
 * Manages SaaS tenant provisioning and lifecycle.
 * Integrates with AuditService for logging critical actions.
 */
class MultiTenantService
{
    protected TenantModel $model;

    protected ?AuditService $auditService = null;

    public function __construct(?AuditService $auditService = null)
    {
        $this->model = new TenantModel();

        // Try to inject AuditService
        try {
            $this->auditService = $auditService ?? new AuditService();
        } catch (\Throwable $e) {
            // AuditService not available, continue without it
            log_message('debug', 'AuditService not available: ' . $e->getMessage());
        }
    }

    /**
     * Get all tenants, optionally filtered by status.
     */
    public function getAll(?string $status = null): array
    {
        if ($status) {
            return $this->model->where('status', $status)->findAll();
        }

        return $this->model->findAll();
    }

    /**
     * Get a single tenant by ID.
     */
    public function getById(int $id): ?array
    {
        return $this->model->find($id);
    }

    /**
     * Get tenant by subdomain.
     */
    public function getBySubdomain(string $subdomain): ?array
    {
        return $this->model->getBySubdomain($subdomain);
    }

    /**
     * Get active tenants.
     */
    public function getActive(): array
    {
        return $this->model->getActive();
    }

    /**
     * Create a new tenant.
     */
    public function create(array $data): int|false
    {
        // Set defaults
        if (!isset($data['status'])) {
            $data['status'] = 'pending';
        }
        if (!isset($data['tier'])) {
            $data['tier'] = 'free';
        }
        if (!isset($data['storage_quota_mb'])) {
            $data['storage_quota_mb'] = 5000; // Default 5GB
        }
        if (!isset($data['storage_used_mb'])) {
            $data['storage_used_mb'] = 0;
        }

        // Handle JSON fields
        if (isset($data['settings']) && is_array($data['settings'])) {
            $data['settings'] = json_encode($data['settings']);
        }
        if (isset($data['features']) && is_array($data['features'])) {
            $data['features'] = json_encode($data['features']);
        }

        $result = $this->model->insert($data);

        if ($result && $this->auditService) {
            try {
                $this->auditService->recordEvent(
                    'multitenant.tenant.created',
                    'create',
                    [
                        'actor_id' => session()->get('user_id'),
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
     * Update an existing tenant.
     */
    public function update(int $id, array $data): bool
    {
        // Get before state for audit
        $before = $this->getById($id);

        if (!$before) {
            return false;
        }

        // Handle JSON fields
        if (isset($data['settings']) && is_array($data['settings'])) {
            $data['settings'] = json_encode($data['settings']);
        }
        if (isset($data['features']) && is_array($data['features'])) {
            $data['features'] = json_encode($data['features']);
        }

        $result = $this->model->update($id, $data);

        if ($result && $this->auditService) {
            try {
                $this->auditService->recordEvent(
                    'multitenant.tenant.updated',
                    'update',
                    [
                        'actor_id' => session()->get('user_id'),
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
     * Delete a tenant.
     */
    public function delete(int $id): bool
    {
        // Get before state for audit
        $before = $this->getById($id);

        if (!$before) {
            return false;
        }

        $result = $this->model->delete($id);

        if ($result && $this->auditService) {
            try {
                $this->auditService->recordEvent(
                    'multitenant.tenant.deleted',
                    'delete',
                    [
                        'actor_id' => session()->get('user_id'),
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
     * Activate a tenant.
     */
    public function activate(int $id): bool
    {
        return $this->update($id, [
            'status' => 'active',
            'activated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Suspend a tenant.
     */
    public function suspend(int $id): bool
    {
        return $this->update($id, [
            'status' => 'suspended',
            'suspended_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Cancel a tenant.
     */
    public function cancel(int $id): bool
    {
        return $this->update($id, [
            'status' => 'cancelled',
            'cancelled_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get request metadata for audit logging.
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
