<?php

namespace Modules\Scheduler\Models;

use CodeIgniter\Model;

/**
 * JobQueueModel - Manages background job queue.
 */
class JobQueueModel extends Model
{
    protected $table = 'job_queue';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = false;

    protected $protectFields = true;

    protected $allowedFields = [
        'queue_name',
        'job_class',
        'job_method',
        'payload',
        'school_id',
        'priority',
        'attempts',
        'max_attempts',
        'available_at',
        'reserved_at',
        'reserved_by',
    ];

    protected $useTimestamps = true;

    protected $createdField = 'created_at';

    protected $updatedField = '';

    protected array $casts = [
        'id' => 'int',
        'school_id' => '?int',
        'priority' => 'int',
        'attempts' => 'int',
        'max_attempts' => 'int',
        'payload' => 'json-array',
    ];

    /**
     * Add a job to the queue.
     *
     * @param array<string, mixed> $payload
     */
    public function push(string $jobClass, array $payload, ?int $schoolId = null, string $queue = 'default', int $priority = 0, int $delaySeconds = 0): int
    {
        $availableAt = date('Y-m-d H:i:s', time() + $delaySeconds);
        $this->insert([
            'queue_name' => $queue,
            'job_class' => $jobClass,
            'job_method' => 'handle',
            'payload' => json_encode($payload),
            'school_id' => $schoolId,
            'priority' => $priority,
            'available_at' => $availableAt,
        ]);
        return (int) $this->getInsertID();
    }

    /**
     * Get the next available job from the queue.
     *
     * @return array<string, mixed>|null
     */
    public function pop(string $queue = 'default', string $workerId = ''): ?array
    {
        $now = date('Y-m-d H:i:s');

        // Use database transaction to ensure atomicity
        $this->db->transStart();

        $job = $this->where('queue_name', $queue)
            ->where('available_at <=', $now)
            ->where('reserved_at IS NULL')
            ->orderBy('priority', 'DESC')
            ->orderBy('available_at', 'ASC')
            ->first();

        if ($job) {
            $this->update($job['id'], [
                'reserved_at' => $now,
                'reserved_by' => $workerId,
                'attempts' => $job['attempts'] + 1,
            ]);
        }

        $this->db->transComplete();

        return $this->db->transStatus() ? $job : null;
    }

    /**
     * Release a job back to the queue (for retry).
     */
    public function release(int $jobId, int $delaySeconds = 0): bool
    {
        $availableAt = date('Y-m-d H:i:s', time() + $delaySeconds);
        return $this->update($jobId, [
            'reserved_at' => null,
            'reserved_by' => null,
            'available_at' => $availableAt,
        ]);
    }

    /**
     * Delete a completed job.
     */
    public function complete(int $jobId): bool
    {
        return $this->delete($jobId);
    }

    /**
     * Get queue statistics.
     *
     * @return array<string, int>
     */
    public function getStats(string $queue = 'default'): array
    {
        $now = date('Y-m-d H:i:s');
        return [
            'pending' => $this->where('queue_name', $queue)
                ->where('reserved_at IS NULL')
                ->where('available_at <=', $now)
                ->countAllResults(),
            'reserved' => $this->where('queue_name', $queue)
                ->where('reserved_at IS NOT NULL')
                ->countAllResults(),
            'delayed' => $this->where('queue_name', $queue)
                ->where('available_at >', $now)
                ->countAllResults(),
        ];
    }
}
