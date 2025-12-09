<?php

namespace Modules\Analytics\Controllers;

use App\Controllers\BaseController;
use App\Modules\Analytics\Services\AnalyticsCrudService;

/**
 * AnalyticsWebController - Handles CRUD operations for analytics dashboards
 * 
 * All data is tenant-scoped by school_id from session.
 */
class AnalyticsWebController extends BaseController
{
    protected AnalyticsCrudService $service;

    public function __construct()
    {
        $this->service = new AnalyticsCrudService();
    }

    /**
     * Get current school ID from session
     */
    protected function getSchoolId(): int
    {
        return (int) (session()->get('school_id') ?? session()->get('schoolID') ?? 1);
    }

    /**
     * Get current user ID from session
     */
    protected function getUserId(): int
    {
        return (int) (session()->get('user_id') ?? session()->get('loginuserID') ?? 1);
    }

    /**
     * List all dashboards
     */
    public function index()
    {
        $schoolId = $this->getSchoolId();
        $userId = $this->getUserId();
        
        $data = [
            'title' => 'Analytics Dashboard',
            'dashboards' => $this->service->getAll($schoolId, $userId),
        ];
        
        return view('Modules\Analytics\Views\index', $data);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $data = [
            'title' => 'Create Dashboard',
        ];
        
        return view('Modules\Analytics\Views\create', $data);
    }

    /**
     * Store new dashboard
     */
    public function store()
    {
        $schoolId = $this->getSchoolId();
        $userId = $this->getUserId();
        
        $validationRules = [
            'name' => 'required|min_length[2]|max_length[150]',
            'description' => 'permit_empty|max_length[1000]',
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'school_id' => $schoolId,
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'is_default' => $this->request->getPost('is_default') ? 1 : 0,
            'is_shared' => $this->request->getPost('is_shared') ? 1 : 0,
            'layout' => [],
            'created_by' => $userId,
        ];

        $id = $this->service->create($data);

        if ($id) {
            return redirect()->to('/analytics')->with('message', 'Dashboard created successfully');
        }
        
        return redirect()->back()->withInput()->with('error', 'Failed to create dashboard');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $schoolId = $this->getSchoolId();
        $dashboard = $this->service->getById($id, $schoolId);
        
        if (!$dashboard) {
            return redirect()->to('/analytics')->with('error', 'Dashboard not found');
        }

        $data = [
            'title' => 'Edit Dashboard',
            'dashboard' => $dashboard,
        ];

        return view('Modules\Analytics\Views\edit', $data);
    }

    /**
     * Update existing dashboard
     */
    public function update($id)
    {
        $schoolId = $this->getSchoolId();

        $validationRules = [
            'name' => 'required|min_length[2]|max_length[150]',
            'description' => 'permit_empty|max_length[1000]',
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'is_default' => $this->request->getPost('is_default') ? 1 : 0,
            'is_shared' => $this->request->getPost('is_shared') ? 1 : 0,
        ];

        $success = $this->service->update($id, $data, $schoolId);

        if ($success) {
            return redirect()->to('/analytics')->with('message', 'Dashboard updated successfully');
        }
        
        return redirect()->back()->withInput()->with('error', 'Failed to update dashboard');
    }

    /**
     * Delete dashboard
     */
    public function delete($id)
    {
        $schoolId = $this->getSchoolId();
        $success = $this->service->delete($id, $schoolId);
        
        if ($success) {
            return redirect()->to('/analytics')->with('message', 'Dashboard deleted successfully');
        }
        
        return redirect()->to('/analytics')->with('error', 'Failed to delete dashboard');
    }
}
