<?php

declare(strict_types=1);

namespace Modules\Orchestration\Agents;

use Modules\Orchestration\Config\OrchestrationConfig;

/**
 * Base Agent Class.
 *
 * Abstract base class for all orchestration agents
 *
 * @version 1.0.0
 */
abstract class BaseAgent
{
    protected OrchestrationConfig $config;

    protected string $runId;

    protected bool $dryRun = false;

    protected array $metrics = [];

    protected array $output = [];

    protected float $startTime;

    public function __construct(OrchestrationConfig $config, string $runId)
    {
        $this->config = $config;
        $this->runId = $runId;
        $this->startTime = microtime(true);
    }

    /**
     * Set dry run mode.
     */
    public function setDryRun(bool $dryRun): void
    {
        $this->dryRun = $dryRun;
    }

    /**
     * Execute the agent.
     */
    abstract public function execute(): array;

    /**
     * Get agent name.
     */
    abstract public function getName(): string;

    /**
     * Get agent description.
     */
    abstract public function getDescription(): string;

    /**
     * Log message.
     */
    protected function log(string $message, string $level = 'info'): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $agentName = $this->getName();
        $logMessage = "[{$timestamp}] [{$level}] [{$agentName}] {$message}";

        echo $logMessage . "\n";
        $this->output[] = $logMessage;
    }

    /**
     * Add metric.
     */
    protected function addMetric(string $key, mixed $value): void
    {
        $this->metrics[$key] = $value;
    }

    /**
     * Execute shell command.
     */
    protected function executeCommand(string $command, string $description = ''): array
    {
        if ($this->dryRun) {
            $this->log("[DRY RUN] Would execute: {$command}", 'info');
            return [
                'success' => true,
                'output' => '[DRY RUN] Command not executed',
                'exit_code' => 0,
            ];
        }

        if ($description) {
            $this->log($description, 'info');
        }

        $output = [];
        $exitCode = 0;

        exec($command . ' 2>&1', $output, $exitCode);

        $result = [
            'success' => $exitCode === 0,
            'output' => implode("\n", $output),
            'exit_code' => $exitCode,
        ];

        if (!$result['success']) {
            $this->log("Command failed with exit code {$exitCode}: {$command}", 'error');
            $this->log("Output: {$result['output']}", 'error');
        }

        return $result;
    }

    /**
     * Create success result.
     */
    protected function createSuccessResult(array $deliverables = []): array
    {
        $duration = microtime(true) - $this->startTime;

        return [
            'success' => true,
            'agent' => $this->getName(),
            'description' => $this->getDescription(),
            'duration' => round($duration, 2),
            'metrics' => $this->metrics,
            'output' => $this->output,
            'deliverables' => $deliverables,
        ];
    }

    /**
     * Create failure result.
     */
    protected function createFailureResult(string $error): array
    {
        $duration = microtime(true) - $this->startTime;

        return [
            'success' => false,
            'agent' => $this->getName(),
            'description' => $this->getDescription(),
            'duration' => round($duration, 2),
            'error' => $error,
            'metrics' => $this->metrics,
            'output' => $this->output,
        ];
    }

    /**
     * Get elapsed time.
     */
    protected function getElapsedTime(): float
    {
        return microtime(true) - $this->startTime;
    }
}
