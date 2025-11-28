<?php

namespace App\Modules\Scheduler\Models;

use CodeIgniter\Model;

/**
 * ScheduledJobModel - Manages scheduled/cron jobs.
 */
class ScheduledJobModel extends Model
{
    protected $table = 'scheduled_jobs';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = false;

    protected $protectFields = true;

    protected $allowedFields = [
        'school_id',
        'name',
        'description',
        'job_class',
        'job_method',
        'parameters',
        'cron_expression',
        'timezone',
        'is_active',
        'run_as_user_id',
        'max_retries',
        'retry_delay_seconds',
        'timeout_seconds',
        'overlap_prevention',
        'last_run_at',
        'last_run_status',
        'next_run_at',
        'created_by',
    ];

    protected $useTimestamps = true;

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';

    protected array $casts = [
        'id' => 'int',
        'school_id' => '?int',
        'is_active' => 'bool',
        'run_as_user_id' => '?int',
        'max_retries' => 'int',
        'retry_delay_seconds' => 'int',
        'timeout_seconds' => 'int',
        'overlap_prevention' => 'bool',
        'created_by' => 'int',
        'parameters' => 'json-array',
    ];

    /**
     * Get all active jobs due for execution.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getDueJobs(): array
    {
        return $this->where('is_active', 1)
            ->where('next_run_at <=', date('Y-m-d H:i:s'))
            ->groupStart()
                ->where('last_run_status !=', 'running')
                ->orWhere('last_run_status IS NULL')
            ->groupEnd()
            ->findAll();
    }

    /**
     * Get jobs by school_id (tenant-scoped).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getBySchool(?int $schoolId = null): array
    {
        if ($schoolId !== null) {
            return $this->where('school_id', $schoolId)->findAll();
        }
        return $this->where('school_id IS NULL')->findAll();
    }

    /**
     * Mark a job as running.
     */
    public function markRunning(int $jobId): bool
    {
        return $this->update($jobId, [
            'last_run_status' => 'running',
            'last_run_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Update job status after execution.
     */
    public function updateStatus(int $jobId, string $status, ?string $nextRunAt = null): bool
    {
        $data = ['last_run_status' => $status];
        if ($nextRunAt !== null) {
            $data['next_run_at'] = $nextRunAt;
        }
        return $this->update($jobId, $data);
    }

    /**
     * Toggle job active status.
     */
    public function toggleActive(int $jobId): bool
    {
        $job = $this->find($jobId);
        if (!$job) {
            return false;
        }
        return $this->update($jobId, ['is_active' => !$job['is_active']]);
    }
}
