<?php

namespace App\Modules\Scheduler\Controllers\Web;

use App\Controllers\BaseController;
use App\Modules\Scheduler\Models\ScheduledJobModel;

/**
 * SchedulerController - Handles CRUD operations for scheduled jobs
 * 
 * All data is tenant-scoped by school_id from session.
 */
class SchedulerController extends BaseController
{
    protected ScheduledJobModel $model;

    public function __construct()
    {
        $this->model = new ScheduledJobModel();
    }

    /**
     * Check if user has permission to access scheduler module
     */
    protected function checkAccess(): bool
    {
        // Allow admins only
        $usertypeID = session()->get('usertypeID');
        $isAdmin = in_array($usertypeID, [0, 1, '0', '1']);
        
        if ($isAdmin) {
            return true;
        }

        // Check scheduler-specific permission if auth service is available
        try {
            $auth = service('auth');
            if ($auth && method_exists($auth, 'can')) {
                return $auth->can('scheduler.view');
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
     * List all scheduled jobs
     */
    public function index()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();
        
        $data = [
            'jobs' => $this->model
                ->where('school_id', $schoolId)
                ->orderBy('created_at', 'DESC')
                ->findAll(),
        ];

        return view('App\Modules\Scheduler\Views\index', $data);
    }

    /**
     * Show create form
     */
    public function create()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        return view('App\Modules\Scheduler\Views\create');
    }

    /**
     * Store a new scheduled job
     */
    public function store()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();
        
        // Validation rules
        $rules = [
            'name'            => 'required|min_length[3]|max_length[150]',
            'job_class'       => 'required|max_length[255]',
            'cron_expression' => 'required|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'school_id'           => $schoolId,
            'name'                => $this->request->getPost('name'),
            'description'         => $this->request->getPost('description') ?: null,
            'job_class'           => $this->request->getPost('job_class'),
            'job_method'          => $this->request->getPost('job_method') ?: 'handle',
            'parameters'          => $this->request->getPost('parameters') ?: null,
            'cron_expression'     => $this->request->getPost('cron_expression'),
            'timezone'            => $this->request->getPost('timezone') ?: 'Africa/Nairobi',
            'is_active'           => (int) ($this->request->getPost('is_active') ?? 1),
            'max_retries'         => (int) ($this->request->getPost('max_retries') ?? 3),
            'retry_delay_seconds' => (int) ($this->request->getPost('retry_delay_seconds') ?? 60),
            'timeout_seconds'     => (int) ($this->request->getPost('timeout_seconds') ?? 3600),
            'overlap_prevention'  => (int) ($this->request->getPost('overlap_prevention') ?? 1),
            'next_run_at'         => date('Y-m-d H:i:s', strtotime('+1 hour')),
        ];

        $result = $this->model->insert($data);

        if ($result) {
            return redirect()->to('/scheduler')->with('message', 'Scheduled job created successfully!');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create scheduled job. Please try again.');
    }

    /**
     * Show edit form
     */
    public function edit(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();
        $job = $this->model
            ->where('school_id', $schoolId)
            ->find($id);
        
        if (!$job) {
            return redirect()->to('/scheduler')->with('error', 'Scheduled job not found.');
        }

        $data = [
            'job' => $job,
        ];

        return view('App\Modules\Scheduler\Views\edit', $data);
    }

    /**
     * Update an existing scheduled job
     */
    public function update(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();

        // Verify job exists
        $existingJob = $this->model
            ->where('school_id', $schoolId)
            ->find($id);
            
        if (!$existingJob) {
            return redirect()->to('/scheduler')->with('error', 'Scheduled job not found.');
        }

        // Validation rules
        $rules = [
            'name'            => 'required|min_length[3]|max_length[150]',
            'job_class'       => 'required|max_length[255]',
            'cron_expression' => 'required|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'                => $this->request->getPost('name'),
            'description'         => $this->request->getPost('description') ?: null,
            'job_class'           => $this->request->getPost('job_class'),
            'job_method'          => $this->request->getPost('job_method') ?: 'handle',
            'parameters'          => $this->request->getPost('parameters') ?: null,
            'cron_expression'     => $this->request->getPost('cron_expression'),
            'timezone'            => $this->request->getPost('timezone') ?: 'Africa/Nairobi',
            'is_active'           => (int) ($this->request->getPost('is_active') ?? 1),
            'max_retries'         => (int) ($this->request->getPost('max_retries') ?? 3),
            'retry_delay_seconds' => (int) ($this->request->getPost('retry_delay_seconds') ?? 60),
            'timeout_seconds'     => (int) ($this->request->getPost('timeout_seconds') ?? 3600),
            'overlap_prevention'  => (int) ($this->request->getPost('overlap_prevention') ?? 1),
        ];

        $result = $this->model
            ->where('school_id', $schoolId)
            ->update($id, $data);

        if ($result) {
            return redirect()->to('/scheduler')->with('message', 'Scheduled job updated successfully!');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update scheduled job. Please try again.');
    }

    /**
     * Delete a scheduled job
     */
    public function delete(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();
        
        // Verify job exists
        $job = $this->model
            ->where('school_id', $schoolId)
            ->find($id);
            
        if (!$job) {
            return redirect()->to('/scheduler')->with('error', 'Scheduled job not found.');
        }

        $result = $this->model
            ->where('school_id', $schoolId)
            ->delete($id);

        if ($result) {
            return redirect()->to('/scheduler')->with('message', 'Scheduled job deleted successfully!');
        }

        return redirect()->to('/scheduler')->with('error', 'Failed to delete scheduled job. Please try again.');
    }
}
