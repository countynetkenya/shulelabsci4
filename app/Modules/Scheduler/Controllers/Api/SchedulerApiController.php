<?php

namespace Modules\Scheduler\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Modules\Scheduler\Models\ScheduledJobModel;
use Modules\Scheduler\Services\SchedulerService;

/**
 * API controller for scheduled jobs management.
 */
class SchedulerApiController extends ResourceController
{
    protected $format = 'json';

    private SchedulerService $scheduler;

    private ScheduledJobModel $jobModel;

    public function __construct()
    {
        $this->scheduler = new SchedulerService();
        $this->jobModel = new ScheduledJobModel();
    }

    /**
     * GET /api/v1/scheduler/jobs.
     */
    public function index()
    {
        $schoolId = session('school_id');
        $jobs = $this->jobModel->getBySchool($schoolId);

        return $this->respond([
            'status' => 'success',
            'data' => $jobs,
        ]);
    }

    /**
     * GET /api/v1/scheduler/jobs/{id}.
     */
    public function show($id = null)
    {
        $job = $this->jobModel->find($id);
        if (!$job) {
            return $this->failNotFound('Job not found');
        }

        // Add schedule description
        $job['schedule_description'] = $this->scheduler->describeSchedule($job['cron_expression']);

        return $this->respond([
            'status' => 'success',
            'data' => $job,
        ]);
    }

    /**
     * POST /api/v1/scheduler/jobs.
     */
    public function create()
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[150]',
            'job_class' => 'required|max_length[255]',
            'cron_expression' => 'required|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->request->getJSON(true);
        $data['created_by'] = session('user_id') ?? 1;
        $data['school_id'] = session('school_id');

        // Validate cron expression
        if (!$this->scheduler->validateCronExpression($data['cron_expression'])) {
            return $this->failValidationErrors(['cron_expression' => 'Invalid cron expression']);
        }

        try {
            $jobId = $this->scheduler->createJob($data);
            return $this->respondCreated([
                'status' => 'success',
                'message' => 'Job created successfully',
                'data' => ['id' => $jobId],
            ]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * PUT /api/v1/scheduler/jobs/{id}.
     */
    public function update($id = null)
    {
        $job = $this->jobModel->find($id);
        if (!$job) {
            return $this->failNotFound('Job not found');
        }

        $data = $this->request->getJSON(true);

        // Validate cron expression if provided
        if (isset($data['cron_expression']) && !$this->scheduler->validateCronExpression($data['cron_expression'])) {
            return $this->failValidationErrors(['cron_expression' => 'Invalid cron expression']);
        }

        try {
            $this->scheduler->updateJob($id, $data);
            return $this->respond([
                'status' => 'success',
                'message' => 'Job updated successfully',
            ]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * DELETE /api/v1/scheduler/jobs/{id}.
     */
    public function delete($id = null)
    {
        $job = $this->jobModel->find($id);
        if (!$job) {
            return $this->failNotFound('Job not found');
        }

        $this->jobModel->delete($id);

        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'Job deleted successfully',
        ]);
    }

    /**
     * POST /api/v1/scheduler/jobs/{id}/toggle.
     */
    public function toggle($id = null)
    {
        $job = $this->jobModel->find($id);
        if (!$job) {
            return $this->failNotFound('Job not found');
        }

        $this->jobModel->toggleActive($id);
        $newStatus = !$job['is_active'];

        return $this->respond([
            'status' => 'success',
            'message' => $newStatus ? 'Job enabled' : 'Job disabled',
            'data' => ['is_active' => $newStatus],
        ]);
    }

    /**
     * POST /api/v1/scheduler/jobs/{id}/run.
     */
    public function run($id = null)
    {
        $job = $this->jobModel->find($id);
        if (!$job) {
            return $this->failNotFound('Job not found');
        }

        try {
            $runId = $this->scheduler->triggerJob($id, session('user_id'));
            return $this->respond([
                'status' => 'success',
                'message' => 'Job triggered successfully',
                'data' => ['run_id' => $runId],
            ]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * GET /api/v1/scheduler/dashboard.
     */
    public function dashboard()
    {
        $schoolId = session('school_id');
        $stats = $this->scheduler->getDashboardStats($schoolId);

        return $this->respond([
            'status' => 'success',
            'data' => $stats,
        ]);
    }
}
