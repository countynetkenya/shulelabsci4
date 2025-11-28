<?php

namespace App\Modules\Scheduler\Models;

use CodeIgniter\Model;

/**
 * JobRunModel - Tracks execution history of scheduled jobs.
 */
class JobRunModel extends Model
{
    protected $table = 'job_runs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'scheduled_job_id',
        'job_class',
        'job_method',
        'parameters',
        'school_id',
        'run_type',
        'status',
        'attempt_number',
        'started_at',
        'completed_at',
        'duration_ms',
        'result_summary',
        'error_message',
        'error_trace',
        'output_log',
        'triggered_by',
        'worker_id',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';

    protected $casts = [
        'id' => 'int',
        'scheduled_job_id' => '?int',
        'school_id' => '?int',
        'attempt_number' => 'int',
        'duration_ms' => '?int',
        'triggered_by' => '?int',
        'parameters' => 'json-array',
    ];

    /**
     * Create a new run record.
     *
     * @param array<string, mixed> $data
     */
    public function createRun(array $data): int
    {
        $this->insert($data);
        return (int) $this->getInsertID();
    }

    /**
     * Mark run as started.
     */
    public function markStarted(int $runId, string $workerId): bool
    {
        return $this->update($runId, [
            'status' => 'running',
            'started_at' => date('Y-m-d H:i:s'),
            'worker_id' => $workerId,
        ]);
    }

    /**
     * Mark run as completed (success or failure).
     */
    public function markCompleted(int $runId, string $status, ?string $summary = null, ?string $errorMessage = null, ?string $errorTrace = null): bool
    {
        $startedAt = $this->select('started_at')->find($runId)['started_at'] ?? null;
        $durationMs = null;
        if ($startedAt) {
            $durationMs = (int) ((strtotime(date('Y-m-d H:i:s')) - strtotime($startedAt)) * 1000);
        }

        return $this->update($runId, [
            'status' => $status,
            'completed_at' => date('Y-m-d H:i:s'),
            'duration_ms' => $durationMs,
            'result_summary' => $summary,
            'error_message' => $errorMessage,
            'error_trace' => $errorTrace,
        ]);
    }

    /**
     * Get recent runs for a scheduled job.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRecentRuns(int $scheduledJobId, int $limit = 20): array
    {
        return $this->where('scheduled_job_id', $scheduledJobId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get runs by status.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getByStatus(string $status, ?int $schoolId = null, int $limit = 100): array
    {
        $builder = $this->where('status', $status);
        if ($schoolId !== null) {
            $builder->where('school_id', $schoolId);
        }
        return $builder->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
