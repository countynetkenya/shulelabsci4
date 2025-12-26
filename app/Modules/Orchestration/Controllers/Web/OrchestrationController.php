<?php

namespace Modules\Orchestration\Controllers\Web;

use App\Controllers\BaseController;
use Modules\Orchestration\Services\OrchestrationService;

class OrchestrationController extends BaseController
{
    protected OrchestrationService $service;

    public function __construct()
    {
        $this->service = new OrchestrationService();
    }

    public function index()
    {
        if (!session()->get('is_logged_in')) {
            return redirect()->to('/login');
        }
        $schoolId = session()->get('school_id') ?? 1;
        $data = ['title' => 'Orchestration Workflows', 'workflows' => $this->service->getAll($schoolId), 'statistics' => $this->service->getStatistics($schoolId)];
        return view('Modules\Orchestration\Views\workflows\index', $data);
    }

    public function create()
    {
        if (!session()->get('is_logged_in')) {
            return redirect()->to('/login');
        }
        return view('Modules\Orchestration\Views\workflows\create', ['title' => 'Create Workflow']);
    }

    public function store()
    {
        if (!session()->get('is_logged_in')) {
            return redirect()->to('/login');
        }
        $schoolId = session()->get('school_id') ?? 1;
        $userId = session()->get('user_id') ?? 1;
        $rules = ['name' => 'required|max_length[200]', 'steps' => 'permit_empty'];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $steps = $this->request->getPost('steps');
        $stepCount = $steps ? count(explode("\n", trim($steps))) : 0;
        $data = ['school_id' => $schoolId, 'name' => $this->request->getPost('name'), 'description' => $this->request->getPost('description'), 'steps' => $steps, 'status' => 'pending', 'current_step' => 0, 'total_steps' => $stepCount, 'created_by' => $userId];
        if ($this->service->create($data)) {
            return redirect()->to('/orchestration')->with('success', 'Workflow created successfully');
        }
        return redirect()->back()->withInput()->with('error', 'Failed to create workflow');
    }

    public function edit($id)
    {
        if (!session()->get('is_logged_in')) {
            return redirect()->to('/login');
        }
        $schoolId = session()->get('school_id') ?? 1;
        $workflow = $this->service->getById($id, $schoolId);
        if (!$workflow) {
            return redirect()->to('/orchestration')->with('error', 'Workflow not found');
        }
        return view('Modules\Orchestration\Views\workflows\edit', ['title' => 'Edit Workflow', 'workflow' => $workflow]);
    }

    public function update($id)
    {
        if (!session()->get('is_logged_in')) {
            return redirect()->to('/login');
        }
        $rules = ['name' => 'required|max_length[200]', 'status' => 'required|in_list[pending,running,completed,failed,paused]'];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $data = ['name' => $this->request->getPost('name'), 'description' => $this->request->getPost('description'), 'status' => $this->request->getPost('status')];
        if ($this->service->update($id, $data)) {
            return redirect()->to('/orchestration')->with('success', 'Workflow updated successfully');
        }
        return redirect()->back()->withInput()->with('error', 'Failed to update workflow');
    }

    public function delete($id)
    {
        if (!session()->get('is_logged_in')) {
            return redirect()->to('/login');
        }
        if ($this->service->delete($id)) {
            return redirect()->to('/orchestration')->with('success', 'Workflow deleted successfully');
        }
        return redirect()->to('/orchestration')->with('error', 'Failed to delete workflow');
    }
}
