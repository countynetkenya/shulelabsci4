<?php

namespace App\Modules\ApprovalWorkflows\Controllers\Web;

use App\Controllers\BaseController;
use App\Modules\ApprovalWorkflows\Services\ApprovalService;

/**
 * ApprovalController - Handles CRUD operations for approval workflows.
 *
 * All data is tenant-scoped by school_id from session.
 */
class ApprovalController extends BaseController
{
    protected ApprovalService $service;

    public function __construct()
    {
        $this->service = new ApprovalService();
    }

    /**
     * Check if user has permission to access approval workflows module.
     */
    protected function checkAccess(): bool
    {
        $usertypeID = session()->get('usertypeID');
        $isAdmin = in_array($usertypeID, [0, 1, '0', '1']);

        if ($isAdmin) {
            return true;
        }

        try {
            $auth = service('auth');
            if ($auth && method_exists($auth, 'can')) {
                return $auth->can('approvals.view');
            }
        } catch (\Throwable $e) {
            // Auth service not available
        }

        return $isAdmin;
    }

    /**
     * Get current school ID from session.
     */
    protected function getSchoolId(): int
    {
        return (int) (session()->get('school_id') ?? session()->get('schoolID') ?? 1);
    }

    /**
     * List all approval requests.
     */
    public function index()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();

        $filters = [
            'search'   => $this->request->getGet('search'),
            'status'   => $this->request->getGet('status'),
            'priority' => $this->request->getGet('priority'),
        ];

        $data = [
            'requests' => $this->service->getAll($schoolId, array_filter($filters)),
            'statuses' => $this->service->getStatuses($schoolId),
            'summary'  => $this->service->getSummary($schoolId),
            'filters'  => $filters,
        ];

        return view('App\Modules\ApprovalWorkflows\Views\index', $data);
    }

    /**
     * Show create form.
     */
    public function create()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        return view('App\Modules\ApprovalWorkflows\Views\create');
    }

    /**
     * Store a new approval request.
     */
    public function store()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();

        $rules = [
            'workflow_id' => 'required|integer',
            'entity_type' => 'required|max_length[100]',
            'entity_id'   => 'required|integer',
            'priority'    => 'permit_empty|in_list[low,normal,high,urgent]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'school_id'    => $schoolId,
            'workflow_id'  => $this->request->getPost('workflow_id'),
            'entity_type'  => $this->request->getPost('entity_type'),
            'entity_id'    => $this->request->getPost('entity_id'),
            'request_data' => $this->request->getPost('request_data') ?: '{}',
            'priority'     => $this->request->getPost('priority') ?: 'normal',
            'requested_by' => session()->get('user_id') ?? 1,
            'requested_at' => date('Y-m-d H:i:s'),
        ];

        $result = $this->service->create($data);

        if ($result) {
            return redirect()->to('/approvals')->with('message', 'Approval request created successfully!');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create request. Please try again.');
    }

    /**
     * Show edit form.
     */
    public function edit(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();
        $request = $this->service->getById($id, $schoolId);

        if (!$request) {
            return redirect()->to('/approvals')->with('error', 'Approval request not found.');
        }

        $data = [
            'request' => $request,
        ];

        return view('App\Modules\ApprovalWorkflows\Views\edit', $data);
    }

    /**
     * Update an existing approval request.
     */
    public function update(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();

        $existingRequest = $this->service->getById($id, $schoolId);
        if (!$existingRequest) {
            return redirect()->to('/approvals')->with('error', 'Approval request not found.');
        }

        $rules = [
            'workflow_id' => 'required|integer',
            'entity_type' => 'required|max_length[100]',
            'entity_id'   => 'required|integer',
            'status'      => 'required|in_list[pending,in_progress,approved,rejected,cancelled,expired]',
            'priority'    => 'permit_empty|in_list[low,normal,high,urgent]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'workflow_id' => $this->request->getPost('workflow_id'),
            'entity_type' => $this->request->getPost('entity_type'),
            'entity_id'   => $this->request->getPost('entity_id'),
            'status'      => $this->request->getPost('status'),
            'priority'    => $this->request->getPost('priority') ?: 'normal',
        ];

        // Set completed_at if status changed to approved/rejected
        if (in_array($data['status'], ['approved', 'rejected']) && empty($existingRequest['completed_at'])) {
            $data['completed_at'] = date('Y-m-d H:i:s');
        }

        $result = $this->service->update($id, $data, $schoolId);

        if ($result) {
            return redirect()->to('/approvals')->with('message', 'Approval request updated successfully!');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update request. Please try again.');
    }

    /**
     * Delete an approval request.
     */
    public function delete(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();

        $request = $this->service->getById($id, $schoolId);
        if (!$request) {
            return redirect()->to('/approvals')->with('error', 'Approval request not found.');
        }

        $result = $this->service->delete($id, $schoolId);

        if ($result) {
            return redirect()->to('/approvals')->with('message', 'Approval request deleted successfully!');
        }

        return redirect()->to('/approvals')->with('error', 'Failed to delete request. Please try again.');
    }
}
