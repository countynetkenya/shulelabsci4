<?php

namespace App\Modules\Admin\Services;

use App\Modules\Admin\Models\AdminSettingModel;
use Modules\Foundation\Services\AuditService;

/**
 * AdminService - Business logic for admin settings management
 * 
 * Manages system-wide configuration settings.
 * Integrates with AuditService for logging critical actions.
 */
class AdminService
{
    protected AdminSettingModel $model;
    protected ?AuditService $auditService = null;

    public function __construct(?AuditService $auditService = null)
    {
        $this->model = new AdminSettingModel();
        
        // Try to inject AuditService
        try {
            $this->auditService = $auditService ?? new AuditService();
        } catch (\Throwable $e) {
            // AuditService not available, continue without it
            log_message('debug', 'AuditService not available: ' . $e->getMessage());
        }
    }

    /**
     * Get all settings, optionally filtered by class
     */
    public function getAll(?string $class = null): array
    {
        if ($class) {
            return $this->model->getByClass($class);
        }
        
        return $this->model->findAll();
    }

    /**
     * Get a single setting by ID
     */
    public function getById(int $id): ?array
    {
        return $this->model->find($id);
    }

    /**
     * Get a setting by class and key
     */
    public function getSetting(string $class, string $key): ?array
    {
        return $this->model->getSetting($class, $key);
    }

    /**
     * Create a new setting
     */
    public function create(array $data): int|false
    {
        $result = $this->model->insert($data);

        if ($result && $this->auditService) {
            try {
                $this->auditService->recordEvent(
                    'admin.setting.created',
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
     * Update an existing setting
     */
    public function update(int $id, array $data): bool
    {
        // Get before state for audit
        $before = $this->getById($id);
        
        if (!$before) {
            return false;
        }

        $result = $this->model->update($id, $data);

        if ($result && $this->auditService) {
            try {
                $this->auditService->recordEvent(
                    'admin.setting.updated',
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
     * Delete a setting
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
                    'admin.setting.deleted',
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
     * Get all unique setting classes (categories)
     */
    public function getClasses(): array
    {
        return $this->model->getClasses();
    }

    /**
     * Set or update a setting value
     */
    public function setSetting(string $class, string $key, $value, string $type = 'string', string $context = 'app'): bool
    {
        return $this->model->setSetting($class, $key, $value, $type, $context);
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
