<?php

namespace App\Modules\MultiTenant\Controllers\Web;

use App\Controllers\BaseController;
use App\Modules\MultiTenant\Services\MultiTenantService;

/**
 * TenantController - Handles CRUD operations for tenants/schools.
 *
 * Manages tenant provisioning and lifecycle in SaaS environment.
 */
class TenantController extends BaseController
{
    protected MultiTenantService $service;

    public function __construct()
    {
        $this->service = new MultiTenantService();
    }

    /**
     * Check if user has permission to access multitenant module
     * (Super Admin only).
     */
    protected function checkAccess(): bool
    {
        // Only super admins (usertypeID = 0) can manage tenants
        $usertypeID = session()->get('usertypeID');
        return in_array($usertypeID, [0, '0']);
    }

    /**
     * List all tenants.
     */
    public function index()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Super Admin only.');
        }

        // Get filter parameters
        $statusFilter = $this->request->getGet('status');

        $data = [
            'tenants' => $this->service->getAll($statusFilter),
            'filter'  => $statusFilter,
        ];

        return view('App\Modules\MultiTenant\Views\tenants\index', $data);
    }

    /**
     * Show create form.
     */
    public function create()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Super Admin only.');
        }

        return view('App\Modules\MultiTenant\Views\tenants\create');
    }

    /**
     * Store a new tenant.
     */
    public function store()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Super Admin only.');
        }

        // Validation rules
        $rules = [
            'name'            => 'required|max_length[200]',
            'subdomain'       => 'required|max_length[63]|alpha_dash|is_unique[tenants.subdomain]',
            'custom_domain'   => 'permit_empty|max_length[255]',
            'status'          => 'permit_empty|in_list[pending,active,suspended,cancelled]',
            'tier'            => 'permit_empty|in_list[free,starter,professional,enterprise]',
            'storage_quota_mb' => 'permit_empty|integer|greater_than[0]',
            'student_quota'   => 'permit_empty|integer|greater_than[0]',
            'staff_quota'     => 'permit_empty|integer|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'            => $this->request->getPost('name'),
            'subdomain'       => strtolower($this->request->getPost('subdomain')),
            'custom_domain'   => $this->request->getPost('custom_domain') ?: null,
            'status'          => $this->request->getPost('status') ?? 'pending',
            'tier'            => $this->request->getPost('tier') ?? 'free',
            'storage_quota_mb' => $this->request->getPost('storage_quota_mb') ?? 5000,
            'student_quota'   => $this->request->getPost('student_quota') ?: null,
            'staff_quota'     => $this->request->getPost('staff_quota') ?: null,
        ];

        $result = $this->service->create($data);

        if ($result) {
            return redirect()->to('/multitenant')->with('message', 'Tenant created successfully!');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create tenant. Please try again.');
    }

    /**
     * Show edit form.
     */
    public function edit(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Super Admin only.');
        }

        $tenant = $this->service->getById($id);

        if (!$tenant) {
            return redirect()->to('/multitenant')->with('error', 'Tenant not found.');
        }

        $data = [
            'tenant' => $tenant,
        ];

        return view('App\Modules\MultiTenant\Views\tenants\edit', $data);
    }

    /**
     * Update an existing tenant.
     */
    public function update(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Super Admin only.');
        }

        // Verify tenant exists
        $existingTenant = $this->service->getById($id);
        if (!$existingTenant) {
            return redirect()->to('/multitenant')->with('error', 'Tenant not found.');
        }

        // Validation rules
        $rules = [
            'name'            => 'required|max_length[200]',
            'subdomain'       => "required|max_length[63]|alpha_dash|is_unique[tenants.subdomain,id,{$id}]",
            'custom_domain'   => 'permit_empty|max_length[255]',
            'status'          => 'permit_empty|in_list[pending,active,suspended,cancelled]',
            'tier'            => 'permit_empty|in_list[free,starter,professional,enterprise]',
            'storage_quota_mb' => 'permit_empty|integer|greater_than[0]',
            'student_quota'   => 'permit_empty|integer|greater_than[0]',
            'staff_quota'     => 'permit_empty|integer|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'            => $this->request->getPost('name'),
            'subdomain'       => strtolower($this->request->getPost('subdomain')),
            'custom_domain'   => $this->request->getPost('custom_domain') ?: null,
            'status'          => $this->request->getPost('status') ?? 'pending',
            'tier'            => $this->request->getPost('tier') ?? 'free',
            'storage_quota_mb' => $this->request->getPost('storage_quota_mb') ?? 5000,
            'student_quota'   => $this->request->getPost('student_quota') ?: null,
            'staff_quota'     => $this->request->getPost('staff_quota') ?: null,
        ];

        $result = $this->service->update($id, $data);

        if ($result) {
            return redirect()->to('/multitenant')->with('message', 'Tenant updated successfully!');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update tenant. Please try again.');
    }

    /**
     * Delete a tenant.
     */
    public function delete(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Super Admin only.');
        }

        // Verify tenant exists
        $tenant = $this->service->getById($id);
        if (!$tenant) {
            return redirect()->to('/multitenant')->with('error', 'Tenant not found.');
        }

        $result = $this->service->delete($id);

        if ($result) {
            return redirect()->to('/multitenant')->with('message', 'Tenant deleted successfully!');
        }

        return redirect()->to('/multitenant')->with('error', 'Failed to delete tenant. Please try again.');
    }

    /**
     * Activate a tenant.
     */
    public function activate(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Super Admin only.');
        }

        $result = $this->service->activate($id);

        if ($result) {
            return redirect()->to('/multitenant')->with('message', 'Tenant activated successfully!');
        }

        return redirect()->to('/multitenant')->with('error', 'Failed to activate tenant.');
    }

    /**
     * Suspend a tenant.
     */
    public function suspend(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Super Admin only.');
        }

        $result = $this->service->suspend($id);

        if ($result) {
            return redirect()->to('/multitenant')->with('message', 'Tenant suspended successfully!');
        }

        return redirect()->to('/multitenant')->with('error', 'Failed to suspend tenant.');
    }
}
