<?php

namespace App\Modules\Reports\Controllers\Web;

use App\Controllers\BaseController;
use Modules\Reports\Services\ReportsCrudService;

/**
 * ReportsController - Handles CRUD operations for reports
 * 
 * All data is tenant-scoped by school_id from session.
 */
class ReportsController extends BaseController
{
    protected ReportsCrudService $service;

    public function __construct()
    {
        $this->service = new ReportsCrudService();
    }

    /**
     * Check if user has permission to access reports module
     */
    protected function checkAccess(): bool
    {
        // Allow admins
        $usertypeID = session()->get('usertypeID');
        $isAdmin = in_array($usertypeID, [0, 1, '0', '1']);
        
        if ($isAdmin) {
            return true;
        }

        // Check reports-specific permission if auth service is available
        try {
            $auth = service('auth');
            if ($auth && method_exists($auth, 'can')) {
                return $auth->can('reports.view');
            }
        } catch (\Throwable $e) {
            // Auth service not available
        }

        // Default: require admin for now
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
     * List all reports
     */
    public function index()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();
        
        // Get filter parameters
        $filters = [
            'search'   => $this->request->getGet('search'),
            'status'   => $this->request->getGet('status'),
            'format'   => $this->request->getGet('format'),
        ];

        $data = [
            'reports'    => $this->service->getAll($schoolId, array_filter($filters)),
            'templates'  => $this->service->getTemplates(),
            'filters'    => $filters,
        ];

        return view('App\Modules\Reports\Views\index', $data);
    }

    /**
     * Show create form
     */
    public function create()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $data = [
            'templates' => $this->service->getTemplates(),
        ];

        return view('App\Modules\Reports\Views\create', $data);
    }

    /**
     * Store new report
     */
    public function store()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $rules = [
            'name'       => 'required|max_length[255]',
            'template'   => 'required|max_length[100]',
            'format'     => 'required|in_list[pdf,excel,csv,html]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $schoolId = $this->getSchoolId();
        $userId = session()->get('user_id') ?? session()->get('userID') ?? 1;

        $data = [
            'school_id'    => $schoolId,
            'name'         => $this->request->getPost('name'),
            'description'  => $this->request->getPost('description'),
            'template'     => $this->request->getPost('template'),
            'format'       => $this->request->getPost('format'),
            'schedule'     => $this->request->getPost('schedule'),
            'is_scheduled' => $this->request->getPost('is_scheduled') ? 1 : 0,
            'status'       => $this->request->getPost('status') ?? 'draft',
            'created_by'   => $userId,
        ];

        // Handle parameters as JSON
        $parameters = $this->request->getPost('parameters');
        if ($parameters) {
            $data['parameters'] = is_array($parameters) ? json_encode($parameters) : $parameters;
        }

        $result = $this->service->create($data);

        if ($result) {
            return redirect()->to('/reports')->with('success', 'Report created successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create report.');
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
        $report = $this->service->getById($id, $schoolId);

        if (!$report) {
            return redirect()->to('/reports')->with('error', 'Report not found.');
        }

        $data = [
            'report'    => $report,
            'templates' => $this->service->getTemplates(),
        ];

        return view('App\Modules\Reports\Views\edit', $data);
    }

    /**
     * Update existing report
     */
    public function update(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $rules = [
            'name'       => 'required|max_length[255]',
            'template'   => 'required|max_length[100]',
            'format'     => 'required|in_list[pdf,excel,csv,html]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'         => $this->request->getPost('name'),
            'description'  => $this->request->getPost('description'),
            'template'     => $this->request->getPost('template'),
            'format'       => $this->request->getPost('format'),
            'schedule'     => $this->request->getPost('schedule'),
            'is_scheduled' => $this->request->getPost('is_scheduled') ? 1 : 0,
            'status'       => $this->request->getPost('status') ?? 'draft',
        ];

        // Handle parameters as JSON
        $parameters = $this->request->getPost('parameters');
        if ($parameters) {
            $data['parameters'] = is_array($parameters) ? json_encode($parameters) : $parameters;
        }

        $result = $this->service->update($id, $data);

        if ($result) {
            return redirect()->to('/reports')->with('success', 'Report updated successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update report.');
    }

    /**
     * Delete report
     */
    public function delete(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $result = $this->service->delete($id);

        if ($result) {
            return redirect()->to('/reports')->with('success', 'Report deleted successfully.');
        }

        return redirect()->to('/reports')->with('error', 'Failed to delete report.');
    }
}
