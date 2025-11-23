<?php

namespace Modules\Reports\Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Orchestration Configuration.
 *
 * Configuration for autonomous system orchestration
 */
class Orchestration extends BaseConfig
{
    /**
     * Enable/disable orchestration.
     */
    public bool $enabled = true;

    /**
     * Maximum execution time (seconds).
     */
    public int $timeout = 1800; // 30 minutes

    /**
     * Retry count for transient failures.
     */
    public int $retryCount = 3;

    /**
     * Phase toggles.
     */
    public array $phases = [
        'backup' => true,
        'code_generation' => true,
        'build_validation' => true,
        'merge_integration' => true,
        'deployment' => true,
        'reports' => true,
    ];

    /**
     * Deployment configuration.
     */
    public array $deployment = [
        'staging' => true,
        'production' => true,
        'approval_required' => false,
    ];

    /**
     * Notification settings.
     */
    public array $notifications = [
        'on_start' => true,
        'on_completion' => true,
        'on_error' => true,
        'channels' => ['email', 'slack'],
    ];

    /**
     * Report settings.
     */
    public array $reports = [
        'generate_pdf' => true,
        'generate_html' => true,
        'publish_to_dashboard' => true,
        'email_reports' => true,
    ];

    /**
     * Quality gates.
     */
    public array $qualityGates = [
        'min_test_pass_rate' => 100,
        'min_code_coverage' => 85,
        'max_critical_vulnerabilities' => 0,
        'max_high_vulnerabilities' => 0,
    ];

    /**
     * Performance thresholds.
     */
    public array $performanceThresholds = [
        'max_response_time_p95' => 500, // milliseconds
        'max_error_rate' => 1.0, // percentage
        'min_availability' => 99.9, // percentage
    ];
}
