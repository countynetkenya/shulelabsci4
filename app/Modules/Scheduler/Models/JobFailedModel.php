<?php

namespace App\Modules\Scheduler\Models;

use CodeIgniter\Model;

/**
 * JobFailedModel - Stores failed jobs for inspection and retry.
 */
class JobFailedModel extends Model
{
    protected $table = 'job_failed';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = false;

    protected $protectFields = true;

    protected $allowedFields = [
        'queue_name',
        'job_class',
        'payload',
        'school_id',
        'exception',
        'failed_at',
    ];

    protected $useTimestamps = false;

    protected $casts = [
        'id' => 'int',
        'school_id' => '?int',
        'payload' => 'json-array',
    ];

    /**
     * Record a failed job.
     *
     * @param array<string, mixed> $payload
     */
    public function recordFailure(string $jobClass, array $payload, string $exception, ?int $schoolId = null, string $queueName = 'default'): int
    {
        $this->insert([
            'queue_name' => $queueName,
            'job_class' => $jobClass,
            'payload' => json_encode($payload),
            'school_id' => $schoolId,
            'exception' => $exception,
            'failed_at' => date('Y-m-d H:i:s'),
        ]);
        return (int) $this->getInsertID();
    }

    /**
     * Get recent failed jobs.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRecent(?int $schoolId = null, int $limit = 50): array
    {
        $builder = $this;
        if ($schoolId !== null) {
            $builder = $builder->where('school_id', $schoolId);
        }
        return $builder->orderBy('failed_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Retry a failed job by moving it back to queue.
     */
    public function retry(int $failedJobId): bool
    {
        $failed = $this->find($failedJobId);
        if (!$failed) {
            return false;
        }

        $queueModel = new JobQueueModel();
        $queueModel->push(
            $failed['job_class'],
            $failed['payload'],
            $failed['school_id'],
            $failed['queue_name'] ?? 'default'
        );

        return $this->delete($failedJobId);
    }
}
