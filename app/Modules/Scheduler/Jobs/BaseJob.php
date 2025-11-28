<?php

namespace App\Modules\Scheduler\Jobs;

/**
 * Base class for all scheduled jobs.
 */
abstract class BaseJob
{
    protected ?int $schoolId = null;

    protected ?int $userId = null;

    /**
     * Execute the job.
     *
     * @param array<string, mixed> $parameters
     * @return mixed
     */
    abstract public function handle(array $parameters = []);

    /**
     * Set the school context for tenant-scoped jobs.
     */
    public function setSchoolId(?int $schoolId): self
    {
        $this->schoolId = $schoolId;
        return $this;
    }

    /**
     * Set the user context.
     */
    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * Get the school ID.
     */
    public function getSchoolId(): ?int
    {
        return $this->schoolId;
    }

    /**
     * Log a message (can be overridden for custom logging).
     */
    protected function log(string $message, string $level = 'info'): void
    {
        log_message($level, '[Job] ' . static::class . ": {$message}");
    }
}
