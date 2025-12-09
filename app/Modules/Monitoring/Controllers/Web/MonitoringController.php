<?php

namespace App\Modules\Monitoring\Controllers\Web;

use App\Controllers\BaseController;
use App\Modules\Monitoring\Services\MonitoringCrudService;

/**
 * MonitoringController - Handles CRUD operations for system metrics
 * 
 * All data is tenant-scoped by school_id from session.
 */
class MonitoringController extends BaseController
{
    protected MonitoringCrudService $service;

    public function __construct()
    {
        $this->service = new MonitoringCrudService();
    }

    /**
     * Check if user has permission to access monitoring module
     */
    protected function checkAccess(): bool
    {
        // Allow admins only
        $usertypeID = session()->get('usertypeID');
        $isAdmin = in_array($usertypeID, [0, 1, '0', '1']);
        
        if ($isAdmin) {
            return true;
        }

        // Check monitoring-specific permission if auth service is available
        try {
            $auth = service('auth');
            if ($auth && method_exists($auth, 'can')) {
                return $auth->can('monitoring.view');
            }
        } catch (\Throwable $e) {
            // Auth service not available
        }

        // Default: require admin
        return $isAdmin;
    }

    /**
     * Get current school ID from session
     */
    protected function getSchoolId(): int
    {
        return (int) (session()->get('school_id') ?? session()->get('schoolID') ?? 1);
    }

    /**
     * List all metrics
     */
    public function index()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();
        
        // Get filter parameters
        $filters = [
            'metric_name' => $this->request->getGet('metric_name'),
            'metric_type' => $this->request->getGet('metric_type'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to'),
        ];

        $data = [
            'metrics'      => $this->service->getAll($schoolId, array_filter($filters)),
            'filters'      => $filters,
            'metricNames'  => $this->service->getMetricNames($schoolId),
            'metricTypes'  => $this->service->getMetricTypes(),
        ];

        return view('App\Modules\Monitoring\Views\index', $data);
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
            'metricTypes' => $this->service->getMetricTypes(),
        ];

        return view('App\Modules\Monitoring\Views\create', $data);
    }

    /**
     * Store a new metric
     */
    public function store()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $rules = [
            'metric_name' => 'required|max_length[100]',
            'metric_type' => 'required|in_list[counter,gauge,histogram,summary]',
            'value'       => 'required|decimal',
            'labels'      => 'permit_empty',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $schoolId = $this->getSchoolId();
        $data = [
            'school_id'   => $schoolId,
            'metric_name' => $this->request->getPost('metric_name'),
            'metric_type' => $this->request->getPost('metric_type'),
            'value'       => $this->request->getPost('value'),
            'labels'      => $this->request->getPost('labels') ?: '{}',
            'recorded_at' => $this->request->getPost('recorded_at') ?: date('Y-m-d H:i:s'),
        ];

        if ($this->service->create($data)) {
            return redirect()->to('/monitoring')->with('success', 'Metric created successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create metric.');
    }

    /**
     * Show edit form
     */
    public function edit(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $schoolId = $this->getSchoolId();
        $metric = $this->service->getById($id, $schoolId);

        if (!$metric) {
            return redirect()->to('/monitoring')->with('error', 'Metric not found.');
        }

        $data = [
            'metric'      => $metric,
            'metricTypes' => $this->service->getMetricTypes(),
        ];

        return view('App\Modules\Monitoring\Views\edit', $data);
    }

    /**
     * Update a metric
     */
    public function update(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $schoolId = $this->getSchoolId();
        $metric = $this->service->getById($id, $schoolId);

        if (!$metric) {
            return redirect()->to('/monitoring')->with('error', 'Metric not found.');
        }

        $rules = [
            'metric_name' => 'required|max_length[100]',
            'metric_type' => 'required|in_list[counter,gauge,histogram,summary]',
            'value'       => 'required|decimal',
            'labels'      => 'permit_empty',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $updateData = [
            'metric_name' => $this->request->getPost('metric_name'),
            'metric_type' => $this->request->getPost('metric_type'),
            'value'       => $this->request->getPost('value'),
            'labels'      => $this->request->getPost('labels') ?: '{}',
        ];

        if ($this->service->update($id, $updateData)) {
            return redirect()->to('/monitoring')->with('success', 'Metric updated successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update metric.');
    }

    /**
     * Delete a metric
     */
    public function delete(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $schoolId = $this->getSchoolId();
        $metric = $this->service->getById($id, $schoolId);

        if (!$metric) {
            return redirect()->to('/monitoring')->with('error', 'Metric not found.');
        }

        if ($this->service->delete($id)) {
            return redirect()->to('/monitoring')->with('success', 'Metric deleted successfully.');
        }

        return redirect()->to('/monitoring')->with('error', 'Failed to delete metric.');
    }
}
