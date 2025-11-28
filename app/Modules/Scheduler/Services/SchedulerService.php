<?php

namespace App\Modules\Scheduler\Services;

use App\Modules\Scheduler\Models\JobRunModel;
use App\Modules\Scheduler\Models\ScheduledJobModel;
use CodeIgniter\I18n\Time;

/**
 * Main scheduler service for managing and executing scheduled jobs.
 */
class SchedulerService
{
    private ScheduledJobModel $jobModel;

    private JobRunModel $runModel;

    private CronExpressionParser $cronParser;

    private JobRunner $jobRunner;

    public function __construct(
        ?ScheduledJobModel $jobModel = null,
        ?JobRunModel $runModel = null,
        ?CronExpressionParser $cronParser = null,
        ?JobRunner $jobRunner = null
    ) {
        $this->jobModel = $jobModel ?? new ScheduledJobModel();
        $this->runModel = $runModel ?? new JobRunModel();
        $this->cronParser = $cronParser ?? new CronExpressionParser();
        $this->jobRunner = $jobRunner ?? new JobRunner();
    }

    /**
     * Create a new scheduled job.
     *
     * @param array<string, mixed> $data
     */
    public function createJob(array $data): int
    {
        // Validate cron expression
        if (!$this->cronParser->isValid($data['cron_expression'])) {
            throw new \RuntimeException('Invalid cron expression');
        }

        // Calculate initial next run time
        $timezone = $data['timezone'] ?? 'Africa/Nairobi';
        $data['next_run_at'] = $this->cronParser->getNextRunTime($data['cron_expression'], null, $timezone);

        $this->jobModel->insert($data);
        return (int) $this->jobModel->getInsertID();
    }

    /**
     * Update a scheduled job.
     *
     * @param array<string, mixed> $data
     */
    public function updateJob(int $jobId, array $data): bool
    {
        // If cron expression changed, recalculate next run
        if (isset($data['cron_expression'])) {
            if (!$this->cronParser->isValid($data['cron_expression'])) {
                throw new \RuntimeException('Invalid cron expression');
            }
            $timezone = $data['timezone'] ?? 'Africa/Nairobi';
            $data['next_run_at'] = $this->cronParser->getNextRunTime($data['cron_expression'], null, $timezone);
        }

        return $this->jobModel->update($jobId, $data);
    }

    /**
     * Process all due scheduled jobs.
     *
     * @return array<int, int> Array of job_id => run_id for executed jobs
     */
    public function processDueJobs(): array
    {
        $dueJobs = $this->jobModel->getDueJobs();
        $results = [];

        foreach ($dueJobs as $job) {
            // Check overlap prevention
            if ($job['overlap_prevention'] && $job['last_run_status'] === 'running') {
                continue;
            }

            // Mark as running to prevent overlap
            $this->jobModel->markRunning($job['id']);

            // Execute the job
            $runId = $this->jobRunner->execute(
                $job['job_class'],
                $job['job_method'] ?? 'handle',
                $job['parameters'] ?? [],
                $job['id'],
                $job['school_id'],
                'scheduled',
                $job['run_as_user_id']
            );

            // Get result status
            $run = $this->runModel->find($runId);
            $status = $run['status'] ?? 'failed';

            // Calculate next run time
            $nextRun = $this->cronParser->getNextRunTime(
                $job['cron_expression'],
                null,
                $job['timezone'] ?? 'Africa/Nairobi'
            );

            // Update job status and next run
            $this->jobModel->updateStatus($job['id'], $status, $nextRun);

            $results[$job['id']] = $runId;
        }

        return $results;
    }

    /**
     * Manually trigger a scheduled job.
     */
    public function triggerJob(int $jobId, ?int $triggeredBy = null): int
    {
        $job = $this->jobModel->find($jobId);
        if (!$job) {
            throw new \RuntimeException('Job not found');
        }

        return $this->jobRunner->execute(
            $job['job_class'],
            $job['job_method'] ?? 'handle',
            $job['parameters'] ?? [],
            $jobId,
            $job['school_id'],
            'manual',
            $triggeredBy
        );
    }

    /**
     * Get job dashboard statistics.
     *
     * @return array<string, mixed>
     */
    public function getDashboardStats(?int $schoolId = null): array
    {
        $today = Time::today()->toDateString();

        return [
            'active_jobs' => $this->jobModel->where('is_active', 1)
                ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
                ->countAllResults(),
            'today_success' => $this->runModel->where('status', 'success')
                ->where('DATE(created_at)', $today)
                ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
                ->countAllResults(),
            'today_failed' => $this->runModel->where('status', 'failed')
                ->where('DATE(created_at)', $today)
                ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
                ->countAllResults(),
            'currently_running' => $this->runModel->where('status', 'running')
                ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
                ->countAllResults(),
            'next_runs' => $this->jobModel->where('is_active', 1)
                ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
                ->orderBy('next_run_at', 'ASC')
                ->limit(10)
                ->findAll(),
        ];
    }

    /**
     * Get human-readable schedule description.
     */
    public function describeSchedule(string $cronExpression): string
    {
        return $this->cronParser->describe($cronExpression);
    }

    /**
     * Validate a cron expression.
     */
    public function validateCronExpression(string $expression): bool
    {
        return $this->cronParser->isValid($expression);
    }
}
