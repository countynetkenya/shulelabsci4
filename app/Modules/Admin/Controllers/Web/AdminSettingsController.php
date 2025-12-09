<?php

namespace App\Modules\Admin\Controllers\Web;

use App\Controllers\BaseController;
use App\Modules\Admin\Services\AdminService;

/**
 * AdminSettingsController - Handles CRUD operations for system settings
 * 
 * Manages global application configuration and settings.
 */
class AdminSettingsController extends BaseController
{
    protected AdminService $service;

    public function __construct()
    {
        $this->service = new AdminService();
    }

    /**
     * Check if user has permission to access admin module
     */
    protected function checkAccess(): bool
    {
        // Allow admins only
        $usertypeID = session()->get('usertypeID');
        return in_array($usertypeID, [0, 1, '0', '1']);
    }

    /**
     * List all settings
     */
    public function index()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Admin only.');
        }

        // Get filter parameters
        $classFilter = $this->request->getGet('class');

        $data = [
            'settings' => $this->service->getAll($classFilter),
            'classes'  => $this->service->getClasses(),
            'filter'   => $classFilter,
        ];

        return view('App\Modules\Admin\Views\settings\index', $data);
    }

    /**
     * Show create form
     */
    public function create()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Admin only.');
        }

        $data = [
            'classes' => $this->service->getClasses(),
        ];

        return view('App\Modules\Admin\Views\settings\create', $data);
    }

    /**
     * Store a new setting
     */
    public function store()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Admin only.');
        }

        // Validation rules
        $rules = [
            'class' => 'required|max_length[100]',
            'key'   => 'required|max_length[100]',
            'value' => 'permit_empty',
            'type'  => 'permit_empty|in_list[string,boolean,integer,json]',
            'context' => 'permit_empty|in_list[app,user,system]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'class'   => $this->request->getPost('class'),
            'key'     => $this->request->getPost('key'),
            'value'   => $this->request->getPost('value') ?? '',
            'type'    => $this->request->getPost('type') ?? 'string',
            'context' => $this->request->getPost('context') ?? 'app',
        ];

        $result = $this->service->create($data);

        if ($result) {
            return redirect()->to('/admin/settings')->with('message', 'Setting created successfully!');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create setting. Please try again.');
    }

    /**
     * Show edit form
     */
    public function edit(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Admin only.');
        }

        $setting = $this->service->getById($id);
        
        if (!$setting) {
            return redirect()->to('/admin/settings')->with('error', 'Setting not found.');
        }

        $data = [
            'setting' => $setting,
            'classes' => $this->service->getClasses(),
        ];

        return view('App\Modules\Admin\Views\settings\edit', $data);
    }

    /**
     * Update an existing setting
     */
    public function update(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Admin only.');
        }

        // Verify setting exists
        $existingSetting = $this->service->getById($id);
        if (!$existingSetting) {
            return redirect()->to('/admin/settings')->with('error', 'Setting not found.');
        }

        // Validation rules
        $rules = [
            'class' => 'required|max_length[100]',
            'key'   => 'required|max_length[100]',
            'value' => 'permit_empty',
            'type'  => 'permit_empty|in_list[string,boolean,integer,json]',
            'context' => 'permit_empty|in_list[app,user,system]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'class'   => $this->request->getPost('class'),
            'key'     => $this->request->getPost('key'),
            'value'   => $this->request->getPost('value') ?? '',
            'type'    => $this->request->getPost('type') ?? 'string',
            'context' => $this->request->getPost('context') ?? 'app',
        ];

        $result = $this->service->update($id, $data);

        if ($result) {
            return redirect()->to('/admin/settings')->with('message', 'Setting updated successfully!');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update setting. Please try again.');
    }

    /**
     * Delete a setting
     */
    public function delete(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Admin only.');
        }

        // Verify setting exists
        $setting = $this->service->getById($id);
        if (!$setting) {
            return redirect()->to('/admin/settings')->with('error', 'Setting not found.');
        }

        $result = $this->service->delete($id);

        if ($result) {
            return redirect()->to('/admin/settings')->with('message', 'Setting deleted successfully!');
        }

        return redirect()->to('/admin/settings')->with('error', 'Failed to delete setting. Please try again.');
    }
}
