<?php

declare(strict_types=1);

namespace Modules\Orchestration\Config;

/**
 * Orchestration Configuration
 * 
 * Configuration for the Master Orchestration Agent
 * 
 * @package Modules\Orchestration\Config
 * @version 1.0.0
 */
class OrchestrationConfig
{
    /**
     * Enable/disable orchestration
     */
    public bool $enabled = true;

    /**
     * Maximum execution timeout (seconds)
     */
    public int $timeout = 1800; // 30 minutes

    /**
     * Number of retries on failure
     */
    public int $retryCount = 3;

    /**
     * Phase toggles
     */
    public bool $enableBackupPhase = true;
    public bool $enableCodeGeneration = true;
    public bool $enableBuildValidation = true;
    public bool $enableMergeIntegration = true;
    public bool $enableDeployment = true;
    public bool $enableReports = true;

    /**
     * Deployment targets
     */
    public bool $deployToStaging = true;
    public bool $deployToProduction = false; // Disabled by default for safety
    public bool $productionApprovalRequired = true;

    /**
     * Notification settings
     */
    public bool $notifyOnStart = true;
    public bool $notifyOnCompletion = true;
    public bool $notifyOnError = true;
    public array $notificationChannels = ['log']; // email, slack, log

    /**
     * Report settings
     */
    public bool $generatePdfReports = false; // Disabled for initial implementation
    public bool $generateHtmlReports = true;
    public bool $publishToDashboard = true;
    public bool $emailReports = false;

    /**
     * Backup settings
     */
    public string $backupDirectory = 'backups';
    public int $backupRetentionDays = 30;
    public bool $compressBackups = true;

    /**
     * Code generation settings
     */
    public int $targetLinesOfCode = 4095;
    public array $targetModules = [
        'Foundation',
        'Hr',
        'Finance',
        'Learning',
        'Mobile',
        'Threads',
        'Library',
        'Inventory',
    ];

    /**
     * Test settings
     */
    public int $targetTestCount = 192;
    public float $targetCodeCoverage = 85.5;
    public bool $failOnCoverageMiss = false;

    /**
     * Quality gates
     */
    public bool $enforceCodeStyle = true;
    public bool $enforceStaticAnalysis = true;
    public bool $enforceSecurityScan = true;
    public int $maxCyclomaticComplexity = 10;

    /**
     * Paths
     */
    public string $reportPath = 'writable/reports';
    public string $logsPath = 'writable/logs/orchestration';
    public string $tempPath = 'writable/temp/orchestration';

    /**
     * Constructor
     */
    public function __construct()
    {
        // Load from environment if available
        $this->loadFromEnvironment();
    }

    /**
     * Load configuration from environment variables
     */
    protected function loadFromEnvironment(): void
    {
        $this->enabled = getenv('ORCHESTRATION_ENABLED') !== false 
            ? filter_var(getenv('ORCHESTRATION_ENABLED'), FILTER_VALIDATE_BOOLEAN)
            : $this->enabled;

        $this->timeout = getenv('ORCHESTRATION_TIMEOUT') !== false
            ? (int) getenv('ORCHESTRATION_TIMEOUT')
            : $this->timeout;

        $this->retryCount = getenv('ORCHESTRATION_RETRY_COUNT') !== false
            ? (int) getenv('ORCHESTRATION_RETRY_COUNT')
            : $this->retryCount;

        // Phase toggles
        $this->enableBackupPhase = getenv('ENABLE_BACKUP_PHASE') !== false
            ? filter_var(getenv('ENABLE_BACKUP_PHASE'), FILTER_VALIDATE_BOOLEAN)
            : $this->enableBackupPhase;

        $this->enableCodeGeneration = getenv('ENABLE_CODE_GENERATION') !== false
            ? filter_var(getenv('ENABLE_CODE_GENERATION'), FILTER_VALIDATE_BOOLEAN)
            : $this->enableCodeGeneration;

        $this->enableBuildValidation = getenv('ENABLE_BUILD_VALIDATION') !== false
            ? filter_var(getenv('ENABLE_BUILD_VALIDATION'), FILTER_VALIDATE_BOOLEAN)
            : $this->enableBuildValidation;

        $this->enableMergeIntegration = getenv('ENABLE_MERGE_INTEGRATION') !== false
            ? filter_var(getenv('ENABLE_MERGE_INTEGRATION'), FILTER_VALIDATE_BOOLEAN)
            : $this->enableMergeIntegration;

        $this->enableDeployment = getenv('ENABLE_DEPLOYMENT') !== false
            ? filter_var(getenv('ENABLE_DEPLOYMENT'), FILTER_VALIDATE_BOOLEAN)
            : $this->enableDeployment;

        $this->enableReports = getenv('ENABLE_REPORTS') !== false
            ? filter_var(getenv('ENABLE_REPORTS'), FILTER_VALIDATE_BOOLEAN)
            : $this->enableReports;

        // Deployment settings
        $this->deployToStaging = getenv('DEPLOY_TO_STAGING') !== false
            ? filter_var(getenv('DEPLOY_TO_STAGING'), FILTER_VALIDATE_BOOLEAN)
            : $this->deployToStaging;

        $this->deployToProduction = getenv('DEPLOY_TO_PRODUCTION') !== false
            ? filter_var(getenv('DEPLOY_TO_PRODUCTION'), FILTER_VALIDATE_BOOLEAN)
            : $this->deployToProduction;
    }

    /**
     * Load configuration from file
     */
    public static function fromFile(string $path): self
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("Configuration file not found: {$path}");
        }

        $config = new self();
        $data = include $path;

        foreach ($data as $key => $value) {
            if (property_exists($config, $key)) {
                $config->$key = $value;
            }
        }

        return $config;
    }

    /**
     * Get phase configuration
     */
    public function getPhaseConfig(int $phase): array
    {
        return match ($phase) {
            1 => [
                'name' => 'RESTART & BACKUP',
                'enabled' => $this->enableBackupPhase,
                'timeout' => 300, // 5 minutes
                'description' => 'Complete system backup and clean slate',
            ],
            2 => [
                'name' => 'CODE GENERATION',
                'enabled' => $this->enableCodeGeneration,
                'timeout' => 300, // 5 minutes
                'description' => 'Generate 4,095 lines of specification-compliant code',
            ],
            3 => [
                'name' => 'BUILD & VALIDATION',
                'enabled' => $this->enableBuildValidation,
                'timeout' => 300, // 5 minutes
                'description' => 'Comprehensive build, test, and quality validation',
            ],
            4 => [
                'name' => 'MERGE & INTEGRATION',
                'enabled' => $this->enableMergeIntegration,
                'timeout' => 300, // 5 minutes
                'description' => 'Merge to main branch and create release tag',
            ],
            5 => [
                'name' => 'DEPLOYMENT',
                'enabled' => $this->enableDeployment,
                'timeout' => 300, // 5 minutes
                'description' => 'Automated deployment to staging and production',
            ],
            6 => [
                'name' => 'REPORTS',
                'enabled' => $this->enableReports,
                'timeout' => 300, // 5 minutes
                'description' => 'Generate 9 comprehensive intelligence reports',
            ],
            default => throw new \InvalidArgumentException("Invalid phase number: {$phase}"),
        };
    }

    /**
     * Validate configuration
     */
    public function validate(): array
    {
        $errors = [];

        if ($this->timeout < 60) {
            $errors[] = 'Timeout must be at least 60 seconds';
        }

        if ($this->retryCount < 0) {
            $errors[] = 'Retry count cannot be negative';
        }

        if ($this->targetCodeCoverage < 0 || $this->targetCodeCoverage > 100) {
            $errors[] = 'Target code coverage must be between 0 and 100';
        }

        if (!is_dir(ROOTPATH . $this->reportPath) && !mkdir(ROOTPATH . $this->reportPath, 0755, true)) {
            $errors[] = "Cannot create report directory: {$this->reportPath}";
        }

        return $errors;
    }
}
