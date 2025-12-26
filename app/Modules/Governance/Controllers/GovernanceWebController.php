<?php

namespace Modules\Governance\Controllers;

use App\Controllers\BaseController;
use App\Modules\Governance\Services\GovernanceService;

/**
 * GovernanceWebController - Handles CRUD operations for governance policies.
 *
 * All data is tenant-scoped by school_id from session.
 */
class GovernanceWebController extends BaseController
{
    protected GovernanceService $service;

    public function __construct()
    {
        $this->service = new GovernanceService();
    }

    /**
     * Get current school ID from session.
     */
    protected function getSchoolId(): int
    {
        return (int) (session()->get('school_id') ?? session()->get('schoolID') ?? 1);
    }

    /**
     * Get current user ID from session.
     */
    protected function getUserId(): int
    {
        return (int) (session()->get('user_id') ?? session()->get('loginuserID') ?? 1);
    }

    /**
     * List all policies.
     */
    public function index()
    {
        $schoolId = $this->getSchoolId();

        $data = [
            'title' => 'Governance Dashboard',
            'policies' => $this->service->getAll($schoolId),
            'statistics' => $this->service->getStatistics($schoolId),
            'categories' => $this->service->getCategories($schoolId),
        ];

        return view('Modules\Governance\Views\index', $data);
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $data = [
            'title' => 'Create Policy',
        ];

        return view('Modules\Governance\Views\create', $data);
    }

    /**
     * Store new policy.
     */
    public function store()
    {
        $schoolId = $this->getSchoolId();
        $userId = $this->getUserId();

        $validationRules = [
            'title' => 'required|min_length[3]|max_length[255]',
            'category' => 'required|max_length[50]',
            'content' => 'required',
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'school_id' => $schoolId,
            'title' => $this->request->getPost('title'),
            'category' => $this->request->getPost('category'),
            'content' => $this->request->getPost('content'),
            'summary' => $this->request->getPost('summary'),
            'version' => $this->request->getPost('version') ?? '1.0',
            'status' => $this->request->getPost('status') ?? 'draft',
            'effective_date' => $this->request->getPost('effective_date'),
            'review_date' => $this->request->getPost('review_date'),
            'created_by' => $userId,
        ];

        $id = $this->service->create($data);

        if ($id) {
            return redirect()->to('/governance')->with('message', 'Policy created successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create policy');
    }

    /**
     * Show edit form.
     */
    public function edit($id)
    {
        $schoolId = $this->getSchoolId();
        $policy = $this->service->getById($id, $schoolId);

        if (!$policy) {
            return redirect()->to('/governance')->with('error', 'Policy not found');
        }

        $data = [
            'title' => 'Edit Policy',
            'policy' => $policy,
        ];

        return view('Modules\Governance\Views\edit', $data);
    }

    /**
     * Update existing policy.
     */
    public function update($id)
    {
        $schoolId = $this->getSchoolId();

        $validationRules = [
            'title' => 'required|min_length[3]|max_length[255]',
            'category' => 'required|max_length[50]',
            'content' => 'required',
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'title' => $this->request->getPost('title'),
            'category' => $this->request->getPost('category'),
            'content' => $this->request->getPost('content'),
            'summary' => $this->request->getPost('summary'),
            'version' => $this->request->getPost('version'),
            'status' => $this->request->getPost('status'),
            'effective_date' => $this->request->getPost('effective_date'),
            'review_date' => $this->request->getPost('review_date'),
        ];

        $success = $this->service->update($id, $data, $schoolId);

        if ($success) {
            return redirect()->to('/governance')->with('message', 'Policy updated successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update policy');
    }

    /**
     * Delete policy.
     */
    public function delete($id)
    {
        $schoolId = $this->getSchoolId();
        $success = $this->service->delete($id, $schoolId);

        if ($success) {
            return redirect()->to('/governance')->with('message', 'Policy deleted successfully');
        }

        return redirect()->to('/governance')->with('error', 'Failed to delete policy');
    }
}
