<?php

namespace Modules\Scheduler\Services;

use Modules\Scheduler\Models\JobQueueModel;

/**
 * Dispatches jobs to the queue for asynchronous processing.
 */
class JobDispatcher
{
    private JobQueueModel $queueModel;

    public function __construct(?JobQueueModel $queueModel = null)
    {
        $this->queueModel = $queueModel ?? new JobQueueModel();
    }

    /**
     * Dispatch a job to the queue.
     *
     * @param array<string, mixed> $payload
     */
    public function dispatch(string $jobClass, array $payload = [], ?int $schoolId = null, string $queue = 'default', int $priority = 0): int
    {
        return $this->queueModel->push($jobClass, $payload, $schoolId, $queue, $priority);
    }

    /**
     * Dispatch a job with delay.
     *
     * @param array<string, mixed> $payload
     */
    public function later(int $delaySeconds, string $jobClass, array $payload = [], ?int $schoolId = null, string $queue = 'default', int $priority = 0): int
    {
        return $this->queueModel->push($jobClass, $payload, $schoolId, $queue, $priority, $delaySeconds);
    }

    /**
     * Dispatch multiple jobs at once.
     *
     * @param array<int, array{class: string, payload?: array<string, mixed>, school_id?: int|null, queue?: string, priority?: int}> $jobs
     * @return array<int, int>
     */
    public function bulk(array $jobs, string $defaultQueue = 'default'): array
    {
        $ids = [];
        foreach ($jobs as $index => $job) {
            $ids[$index] = $this->dispatch(
                $job['class'],
                $job['payload'] ?? [],
                $job['school_id'] ?? null,
                $job['queue'] ?? $defaultQueue,
                $job['priority'] ?? 0
            );
        }
        return $ids;
    }
}
