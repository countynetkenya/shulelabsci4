<?php

declare(strict_types=1);

namespace Modules\Orchestration\Services;

use Modules\Orchestration\Config\OrchestrationConfig;
use Modules\Orchestration\Agents\Phase1BackupAgent;
use Modules\Orchestration\Agents\Phase2ACodeGenerationAgent;
use Modules\Orchestration\Agents\Phase2BPortalsAgent;
use Modules\Orchestration\Agents\Phase3ValidationAgent;
use Modules\Orchestration\Agents\Phase4MergeAgent;
use Modules\Orchestration\Agents\Phase5DeploymentAgent;
use Modules\Orchestration\Agents\Phase6ReportsAgent;
use Modules\Orchestration\Reports\ReportGenerator;

/**
 * Master Orchestration Service
 * 
 * Coordinates all 6 phases of the autonomous system build
 * 
 * @package Modules\Orchestration\Services
 * @version 1.0.0
 */
class MasterOrchestrationService
{
    protected OrchestrationConfig $config;
    protected array $phaseResults = [];
    protected bool $dryRun = false;
    protected string $runId;
    protected float $startTime;

    public function __construct(OrchestrationConfig $config)
    {
        $this->config = $config;
        $this->runId = date('YmdHis') . '-' . substr(md5(uniqid((string) mt_rand(), true)), 0, 8);
        $this->startTime = microtime(true);
        
        // Validate configuration
        $errors = $config->validate();
        if (!empty($errors)) {
            throw new \RuntimeException('Configuration validation failed: ' . implode(', ', $errors));
        }
        
        // Ensure directories exist
        $this->ensureDirectories();
    }

    /**
     * Set dry run mode
     */
    public function setDryRun(bool $dryRun): void
    {
        $this->dryRun = $dryRun;
    }

    /**
     * Execute complete orchestration (all 6 phases)
     */
    public function executeComplete(array $skipPhases = []): array
    {
        $this->log('Starting complete orchestration', 'info');
        $this->log("Run ID: {$this->runId}", 'info');
        
        $results = [
            'success' => true,
            'run_id' => $this->runId,
            'start_time' => date('Y-m-d H:i:s', (int) $this->startTime),
            'phases' => [],
            'metrics' => [],
        ];

        try {
            // Execute all phases in sequence
            for ($phase = 1; $phase <= 6; $phase++) {
                if (in_array($phase, $skipPhases, true)) {
                    $this->log("Skipping Phase {$phase}", 'info');
                    continue;
                }

                $phaseConfig = $this->config->getPhaseConfig($phase);
                
                if (!$phaseConfig['enabled']) {
                    $this->log("Phase {$phase} is disabled in configuration", 'info');
                    continue;
                }

                $phaseResult = $this->executePhase($phase);
                $results['phases'][] = $phaseResult;

                if (!$phaseResult['success']) {
                    $results['success'] = false;
                    $this->log("Phase {$phase} failed, halting orchestration", 'error');
                    break;
                }
            }

            // Generate final report
            if ($this->config->enableReports && $results['success']) {
                $reportPath = $this->generateFinalReport($results);
                $results['report_path'] = $reportPath;
            }

            $duration = microtime(true) - $this->startTime;
            $results['end_time'] = date('Y-m-d H:i:s');
            $results['duration'] = round($duration, 2);

            $this->log("Orchestration completed in {$duration} seconds", 'info');

        } catch (\Throwable $e) {
            $results['success'] = false;
            $results['error'] = $e->getMessage();
            $this->log("Orchestration failed: {$e->getMessage()}", 'error');
        }

        return $results;
    }

    /**
     * Execute a specific phase
     */
    public function executePhase(int $phaseNumber): array
    {
        $phaseConfig = $this->config->getPhaseConfig($phaseNumber);
        $this->log("Starting Phase {$phaseNumber}: {$phaseConfig['name']}", 'info');
        
        $phaseStart = microtime(true);
        
        $result = [
            'number' => $phaseNumber,
            'name' => $phaseConfig['name'],
            'success' => false,
            'start_time' => date('Y-m-d H:i:s', (int) $phaseStart),
            'duration' => 0,
            'metrics' => [],
            'output' => [],
        ];

        try {
            $agent = $this->getAgentForPhase($phaseNumber);
            
            if ($this->dryRun) {
                $agent->setDryRun(true);
            }

            $agentResult = $agent->execute();
            
            $result['success'] = $agentResult['success'];
            $result['metrics'] = $agentResult['metrics'] ?? [];
            $result['output'] = $agentResult['output'] ?? [];
            $result['deliverables'] = $agentResult['deliverables'] ?? [];

            $duration = microtime(true) - $phaseStart;
            $result['duration'] = round($duration, 2);
            $result['end_time'] = date('Y-m-d H:i:s');

            if ($result['success']) {
                $this->log("Phase {$phaseNumber} completed successfully in {$duration} seconds", 'info');
            } else {
                $this->log("Phase {$phaseNumber} failed after {$duration} seconds", 'error');
            }

        } catch (\Throwable $e) {
            $result['success'] = false;
            $result['error'] = $e->getMessage();
            $result['trace'] = $e->getTraceAsString();
            $this->log("Phase {$phaseNumber} exception: {$e->getMessage()}", 'error');
        }

        $this->phaseResults[] = $result;
        return $result;
    }

    /**
     * Get the appropriate agent for a phase
     */
    protected function getAgentForPhase(int $phase): object
    {
        return match ($phase) {
            1 => new Phase1BackupAgent($this->config, $this->runId),
            2 => new Phase2ACodeGenerationAgent($this->config, $this->runId),
            3 => new Phase3ValidationAgent($this->config, $this->runId),
            4 => new Phase4MergeAgent($this->config, $this->runId),
            5 => new Phase5DeploymentAgent($this->config, $this->runId),
            6 => new Phase6ReportsAgent($this->config, $this->runId),
            default => throw new \InvalidArgumentException("Invalid phase number: {$phase}"),
        };
    }

    /**
     * Generate final orchestration report
     */
    protected function generateFinalReport(array $results): string
    {
        $reportDir = ROOTPATH . $this->config->reportPath . '/' . $this->runId;
        
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0755, true);
        }

        $reportFile = $reportDir . '/orchestration-summary.json';
        file_put_contents($reportFile, json_encode($results, JSON_PRETTY_PRINT));

        // Generate HTML report if enabled
        if ($this->config->generateHtmlReports) {
            $generator = new ReportGenerator($this->config);
            $htmlFile = $generator->generateOrchestrationReport($results, $reportDir);
            $this->log("HTML report generated: {$htmlFile}", 'info');
        }

        return $reportDir;
    }

    /**
     * Ensure required directories exist
     */
    protected function ensureDirectories(): void
    {
        $dirs = [
            ROOTPATH . $this->config->reportPath,
            ROOTPATH . $this->config->logsPath,
            ROOTPATH . $this->config->tempPath,
            ROOTPATH . $this->config->backupDirectory,
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    /**
     * Log message
     */
    protected function log(string $message, string $level = 'info'): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}\n";
        
        // Console output
        echo $logMessage;
        
        // File logging
        $logFile = ROOTPATH . $this->config->logsPath . "/orchestration-{$this->runId}.log";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    /**
     * Get run ID
     */
    public function getRunId(): string
    {
        return $this->runId;
    }

    /**
     * Get phase results
     */
    public function getPhaseResults(): array
    {
        return $this->phaseResults;
    }
}
