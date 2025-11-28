<?php

namespace App\Modules\Scheduler\Models;

use CodeIgniter\Model;

/**
 * JobLogModel - Stores detailed logs for job runs.
 */
class JobLogModel extends Model
{
    protected $table = 'job_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'job_run_id',
        'level',
        'message',
        'context',
        'logged_at',
    ];

    protected $useTimestamps = false;

    protected $casts = [
        'id' => 'int',
        'job_run_id' => 'int',
        'context' => 'json-array',
    ];

    /**
     * Add a log entry.
     *
     * @param array<string, mixed>|null $context
     */
    public function addLog(int $jobRunId, string $level, string $message, ?array $context = null): int
    {
        $this->insert([
            'job_run_id' => $jobRunId,
            'level' => $level,
            'message' => $message,
            'context' => $context ? json_encode($context) : null,
            'logged_at' => date('Y-m-d H:i:s'),
        ]);
        return (int) $this->getInsertID();
    }

    /**
     * Get logs for a run.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getLogsForRun(int $jobRunId, ?string $level = null, int $limit = 100): array
    {
        $builder = $this->where('job_run_id', $jobRunId);
        if ($level !== null) {
            $builder->where('level', $level);
        }
        return $builder->orderBy('logged_at', 'ASC')
            ->limit($limit)
            ->findAll();
    }
}
