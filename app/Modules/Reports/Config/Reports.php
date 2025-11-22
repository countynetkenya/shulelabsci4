<?php

declare(strict_types=1);

namespace Modules\Reports\Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Reports Module Configuration
 * 
 * Defines report sources, dimensions, periods, and builder options
 * for the metadata-driven reporting engine.
 */
class Reports extends BaseConfig
{
    /**
     * Default timezone for report generation
     */
    public string $timezone = 'Africa/Nairobi';

    /**
     * Default currency for financial reports
     */
    public string $defaultCurrency = 'KES';

    /**
     * Maximum number of rows in a single report
     */
    public int $maxReportRows = 10000;

    /**
     * Cache TTL for report results (in seconds)
     */
    public int $resultCacheTtl = 3600;

    /**
     * Enable/disable report result caching
     */
    public bool $enableCaching = true;

    /**
     * Default export format
     */
    public string $defaultExportFormat = 'pdf';

    /**
     * Supported export formats
     * 
     * @var array<string>
     */
    public array $supportedExportFormats = ['pdf', 'excel', 'csv', 'json'];

    /**
     * Supported report periods
     * 
     * @var array<string, array{label: string, days: int}>
     */
    public array $periods = [
        'today' => [
            'label' => 'Today',
            'days'  => 1,
        ],
        'yesterday' => [
            'label' => 'Yesterday',
            'days'  => 1,
        ],
        'this_week' => [
            'label' => 'This Week',
            'days'  => 7,
        ],
        'last_week' => [
            'label' => 'Last Week',
            'days'  => 7,
        ],
        'this_month' => [
            'label' => 'This Month',
            'days'  => 30,
        ],
        'last_month' => [
            'label' => 'Last Month',
            'days'  => 30,
        ],
        'this_quarter' => [
            'label' => 'This Quarter',
            'days'  => 90,
        ],
        'last_quarter' => [
            'label' => 'Last Quarter',
            'days'  => 90,
        ],
        'this_year' => [
            'label' => 'This Year',
            'days'  => 365,
        ],
        'last_year' => [
            'label' => 'Last Year',
            'days'  => 365,
        ],
        'custom' => [
            'label' => 'Custom Range',
            'days'  => 0,
        ],
    ];

    /**
     * Available report dimensions
     * 
     * @var array<string, array{label: string, field: string, type: string}>
     */
    public array $dimensions = [
        'date' => [
            'label' => 'Date',
            'field' => 'created_at',
            'type'  => 'datetime',
        ],
        'month' => [
            'label' => 'Month',
            'field' => 'created_at',
            'type'  => 'month',
        ],
        'quarter' => [
            'label' => 'Quarter',
            'field' => 'created_at',
            'type'  => 'quarter',
        ],
        'year' => [
            'label' => 'Year',
            'field' => 'created_at',
            'type'  => 'year',
        ],
        'tenant' => [
            'label' => 'Tenant',
            'field' => 'tenant_id',
            'type'  => 'string',
        ],
        'user' => [
            'label' => 'User',
            'field' => 'user_id',
            'type'  => 'string',
        ],
    ];

    /**
     * Available data sources for reports
     * 
     * @var array<string, array{label: string, module: string, enabled: bool}>
     */
    public array $dataSources = [
        'finance' => [
            'label'   => 'Finance',
            'module'  => 'Finance',
            'enabled' => true,
        ],
        'hr' => [
            'label'   => 'HR & Payroll',
            'module'  => 'Hr',
            'enabled' => true,
        ],
        'inventory' => [
            'label'   => 'Inventory',
            'module'  => 'Inventory',
            'enabled' => true,
        ],
        'learning' => [
            'label'   => 'Learning',
            'module'  => 'Learning',
            'enabled' => true,
        ],
        'library' => [
            'label'   => 'Library',
            'module'  => 'Library',
            'enabled' => true,
        ],
        'threads' => [
            'label'   => 'Threads',
            'module'  => 'Threads',
            'enabled' => true,
        ],
    ];

    /**
     * Available aggregation functions
     * 
     * @var array<string, string>
     */
    public array $aggregations = [
        'sum'     => 'Sum',
        'avg'     => 'Average',
        'count'   => 'Count',
        'min'     => 'Minimum',
        'max'     => 'Maximum',
        'distinct_count' => 'Distinct Count',
    ];

    /**
     * Dashboard refresh interval (in seconds)
     */
    public int $dashboardRefreshInterval = 300;

    /**
     * Mobile API response compression
     */
    public bool $compressMobileResponses = true;

    /**
     * Maximum concurrent report executions
     */
    public int $maxConcurrentExecutions = 5;

    /**
     * Report execution timeout (in seconds)
     */
    public int $executionTimeout = 120;

    /**
     * Enable audit logging for report access
     */
    public bool $auditReportAccess = true;

    /**
     * Get period configuration
     * 
     * @param string $period
     * @return array{label: string, days: int}|null
     */
    public function getPeriod(string $period): ?array
    {
        return $this->periods[$period] ?? null;
    }

    /**
     * Get data source configuration
     * 
     * @param string $source
     * @return array{label: string, module: string, enabled: bool}|null
     */
    public function getDataSource(string $source): ?array
    {
        return $this->dataSources[$source] ?? null;
    }

    /**
     * Check if a data source is enabled
     * 
     * @param string $source
     * @return bool
     */
    public function isDataSourceEnabled(string $source): bool
    {
        $config = $this->getDataSource($source);
        return $config !== null && ($config['enabled'] ?? false);
    }
}
