<?php

namespace App\Modules\Integrations\Controllers\Web;

use App\Controllers\BaseController;
use App\Modules\Integrations\Services\IntegrationsCrudService;

/**
 * IntegrationsController - Handles CRUD operations for third-party integrations
 * 
 * Data can be scoped by tenant_id (school_id) from session.
 */
class IntegrationsController extends BaseController
{
    protected IntegrationsCrudService $service;

    public function __construct()
    {
        $this->service = new IntegrationsCrudService();
    }

    /**
     * Check if user has permission to access integrations module
     */
    protected function checkAccess(): bool
    {
        // Allow admins only
        $usertypeID = session()->get('usertypeID');
        $isAdmin = in_array($usertypeID, [0, 1, '0', '1']);
        
        if ($isAdmin) {
            return true;
        }

        // Check integrations-specific permission if auth service is available
        try {
            $auth = service('auth');
            if ($auth && method_exists($auth, 'can')) {
                return $auth->can('integrations.manage');
            }
        } catch (\Throwable $e) {
            // Auth service not available
        }

        // Default: require admin
        return $isAdmin;
    }

    /**
     * Get current tenant ID from session (using school_id)
     */
    protected function getTenantId(): ?string
    {
        $schoolId = session()->get('school_id') ?? session()->get('schoolID');
        return $schoolId ? (string) $schoolId : null;
    }

    /**
     * List all integrations
     */
    public function index()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $tenantId = $this->getTenantId();
        
        // Get filter parameters
        $filters = [
            'type'      => $this->request->getGet('type'),
            'is_active' => $this->request->getGet('is_active'),
        ];

        $data = [
            'integrations' => $this->service->getAll($tenantId, array_filter($filters)),
            'filters'      => $filters,
            'types'        => $this->service->getIntegrationTypes(),
        ];

        return view('App\Modules\Integrations\Views\index', $data);
    }

    /**
     * Show create form
     */
    public function create()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $data = [
            'types' => $this->service->getIntegrationTypes(),
        ];

        return view('App\Modules\Integrations\Views\create', $data);
    }

    /**
     * Store a new integration
     */
    public function store()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $rules = [
            'name'          => 'required|max_length[100]|is_unique[integration_integrations.name]',
            'type'          => 'required|max_length[50]',
            'adapter_class' => 'required|max_length[255]',
            'config_json'   => 'permit_empty',
            'is_active'     => 'permit_empty|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tenantId = $this->getTenantId();
        $data = [
            'name'          => $this->request->getPost('name'),
            'type'          => $this->request->getPost('type'),
            'adapter_class' => $this->request->getPost('adapter_class'),
            'config_json'   => $this->request->getPost('config_json') ?: '{}',
            'is_active'     => $this->request->getPost('is_active') ?: 1,
            'tenant_id'     => $tenantId,
        ];

        if ($this->service->create($data)) {
            return redirect()->to('/integrations')->with('success', 'Integration created successfully.');
        }

        $errors = $this->service->getErrors();
        return redirect()->back()->withInput()->with('errors', $errors ?: ['error' => 'Failed to create integration.']);
    }

    /**
     * Show edit form
     */
    public function edit(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $tenantId = $this->getTenantId();
        $integration = $this->service->getById($id, $tenantId);

        if (!$integration) {
            return redirect()->to('/integrations')->with('error', 'Integration not found.');
        }

        $data = [
            'integration' => $integration,
            'types'       => $this->service->getIntegrationTypes(),
        ];

        return view('App\Modules\Integrations\Views\edit', $data);
    }

    /**
     * Update an integration
     */
    public function update(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $tenantId = $this->getTenantId();
        $integration = $this->service->getById($id, $tenantId);

        if (!$integration) {
            return redirect()->to('/integrations')->with('error', 'Integration not found.');
        }

        $rules = [
            'name'          => "required|max_length[100]|is_unique[integration_integrations.name,id,{$id}]",
            'type'          => 'required|max_length[50]',
            'adapter_class' => 'required|max_length[255]',
            'config_json'   => 'permit_empty',
            'is_active'     => 'permit_empty|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $updateData = [
            'name'          => $this->request->getPost('name'),
            'type'          => $this->request->getPost('type'),
            'adapter_class' => $this->request->getPost('adapter_class'),
            'config_json'   => $this->request->getPost('config_json') ?: '{}',
            'is_active'     => $this->request->getPost('is_active') ?: 1,
        ];

        if ($this->service->update($id, $updateData)) {
            return redirect()->to('/integrations')->with('success', 'Integration updated successfully.');
        }

        $errors = $this->service->getErrors();
        return redirect()->back()->withInput()->with('errors', $errors ?: ['error' => 'Failed to update integration.']);
    }

    /**
     * Delete an integration
     */
    public function delete(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $tenantId = $this->getTenantId();
        $integration = $this->service->getById($id, $tenantId);

        if (!$integration) {
            return redirect()->to('/integrations')->with('error', 'Integration not found.');
        }

        if ($this->service->delete($id)) {
            return redirect()->to('/integrations')->with('success', 'Integration deleted successfully.');
        }

        return redirect()->to('/integrations')->with('error', 'Failed to delete integration.');
    }
}
