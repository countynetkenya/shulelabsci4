<?php

namespace App\Modules\Scheduler\Services;

use App\Modules\Scheduler\Models\JobQueueModel;
use App\Modules\Scheduler\Models\JobRunModel;
use App\Modules\Scheduler\Models\JobLogModel;
use App\Modules\Scheduler\Models\JobFailedModel;
use Throwable;

/**
 * Executes jobs and handles retry logic with exponential backoff.
 */
class JobRunner
{
    private JobRunModel $runModel;
    private JobLogModel $logModel;
    private JobQueueModel $queueModel;
    private JobFailedModel $failedModel;
    private string $workerId;

    public function __construct(
        ?JobRunModel $runModel = null,
        ?JobLogModel $logModel = null,
        ?JobQueueModel $queueModel = null,
        ?JobFailedModel $failedModel = null
    ) {
        $this->runModel = $runModel ?? new JobRunModel();
        $this->logModel = $logModel ?? new JobLogModel();
        $this->queueModel = $queueModel ?? new JobQueueModel();
        $this->failedModel = $failedModel ?? new JobFailedModel();
        $this->workerId = gethostname() . ':' . getmypid();
    }

    /**
     * Execute a job class.
     *
     * @param array<string, mixed> $parameters
     */
    public function execute(string $jobClass, string $method = 'handle', array $parameters = [], ?int $scheduledJobId = null, ?int $schoolId = null, string $runType = 'manual', ?int $triggeredBy = null): int
    {
        // Create run record
        $runId = $this->runModel->createRun([
            'scheduled_job_id' => $scheduledJobId,
            'job_class' => $jobClass,
            'job_method' => $method,
            'parameters' => json_encode($parameters),
            'school_id' => $schoolId,
            'run_type' => $runType,
            'status' => 'pending',
            'attempt_number' => 1,
            'triggered_by' => $triggeredBy,
        ]);

        $this->log($runId, 'info', 'Job execution started', ['job_class' => $jobClass, 'parameters' => $parameters]);

        // Mark as running
        $this->runModel->markStarted($runId, $this->workerId);

        try {
            // Instantiate and execute the job
            if (!class_exists($jobClass)) {
                throw new \RuntimeException("Job class {$jobClass} does not exist");
            }

            $job = new $jobClass();
            if (!method_exists($job, $method)) {
                throw new \RuntimeException("Method {$method} does not exist on {$jobClass}");
            }

            $result = $job->{$method}($parameters);

            $this->log($runId, 'info', 'Job completed successfully', ['result' => $result]);
            $this->runModel->markCompleted($runId, 'success', is_string($result) ? $result : json_encode($result));

            return $runId;
        } catch (Throwable $e) {
            $this->log($runId, 'error', 'Job failed: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $this->runModel->markCompleted(
                $runId,
                'failed',
                null,
                $e->getMessage(),
                $e->getTraceAsString()
            );

            return $runId;
        }
    }

    /**
     * Process a job from the queue.
     */
    public function processQueue(string $queue = 'default'): bool
    {
        $job = $this->queueModel->pop($queue, $this->workerId);
        if (!$job) {
            return false;
        }

        $this->log(0, 'info', 'Processing queued job', ['job_id' => $job['id'], 'class' => $job['job_class']]);

        try {
            $jobClass = $job['job_class'];
            $method = $job['job_method'] ?? 'handle';
            $payload = $job['payload'];

            if (!class_exists($jobClass)) {
                throw new \RuntimeException("Job class {$jobClass} does not exist");
            }

            $instance = new $jobClass();
            $instance->{$method}($payload);

            $this->queueModel->complete($job['id']);
            return true;
        } catch (Throwable $e) {
            if ($job['attempts'] >= $job['max_attempts']) {
                // Move to failed jobs
                $this->failedModel->recordFailure(
                    $job['job_class'],
                    $job['payload'],
                    $e->getMessage() . "\n" . $e->getTraceAsString(),
                    $job['school_id'],
                    $job['queue_name']
                );
                $this->queueModel->complete($job['id']);
            } else {
                // Retry with exponential backoff
                $delay = $this->calculateRetryDelay($job['attempts']);
                $this->queueModel->release($job['id'], $delay);
            }

            return false;
        }
    }

    /**
     * Calculate retry delay with exponential backoff.
     */
    public function calculateRetryDelay(int $attempt, int $baseDelay = 60): int
    {
        return $baseDelay * (int) pow(2, $attempt - 1);
    }

    /**
     * Add a log entry.
     *
     * @param array<string, mixed>|null $context
     */
    private function log(int $runId, string $level, string $message, ?array $context = null): void
    {
        if ($runId > 0) {
            $this->logModel->addLog($runId, $level, $message, $context);
        }
    }

    /**
     * Get the worker ID.
     */
    public function getWorkerId(): string
    {
        return $this->workerId;
    }
}
