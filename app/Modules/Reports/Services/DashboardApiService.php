<?php

declare(strict_types=1);

namespace Modules\Reports\Services;

use Modules\Reports\Models\ReportModel;
use Modules\Reports\Config\Reports;

/**
 * Service for preparing dashboard and mobile API responses
 * 
 * Optimizes report data for mobile consumption with
 * compression and efficient data structures.
 */
class DashboardApiService
{
    private ReportModel $reportModel;
    private ReportExecutorService $executor;
    private Reports $config;

    public function __construct()
    {
        $this->reportModel = new ReportModel();
        $this->executor = new ReportExecutorService();
        $this->config = config('Reports');
    }

    /**
     * Get dashboard data for a tenant
     * 
     * @param string $tenantId
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function getDashboardData(string $tenantId, array $options = []): array
    {
        $this->reportModel->setTenantId($tenantId);
        
        $widgets = $options['widgets'] ?? [];
        $dashboardData = [
            'tenant_id'  => $tenantId,
            'timestamp'  => time(),
            'widgets'    => [],
        ];

        // Get public reports for dashboard widgets
        $publicReports = $this->reportModel->getPublicReports(10);
        
        foreach ($publicReports as $report) {
            $dashboardData['widgets'][] = $this->prepareWidgetData($report);
        }

        // Apply mobile optimization if requested
        if ($options['mobile'] ?? false) {
            $dashboardData = $this->optimizeForMobile($dashboardData);
        }

        return $dashboardData;
    }

    /**
     * Get widget data for a specific report
     * 
     * @param int $reportId
     * @param string $tenantId
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function getWidgetData(int $reportId, string $tenantId, array $filters = []): array
    {
        $this->reportModel->setTenantId($tenantId);
        $report = $this->reportModel->find($reportId);

        if (!$report) {
            return [
                'error' => 'Report not found',
            ];
        }

        return $this->prepareWidgetData($report, $filters);
    }

    /**
     * Prepare widget data from report
     * 
     * @param array<string, mixed> $report
     * @param array<string, mixed> $customFilters
     * @return array<string, mixed>
     */
    private function prepareWidgetData(array $report, array $customFilters = []): array
    {
        $config = $report['config_json'] ?? [];
        
        // Merge custom filters with report config
        if (!empty($customFilters)) {
            $config['filters'] = array_merge($config['filters'] ?? [], $customFilters);
        }

        $definition = \Modules\Reports\Domain\ReportDefinition::fromArray($config);
        
        // Execute report with caching
        $result = $this->executor->execute($definition, $report['id'], true);
        
        return [
            'id'       => $report['id'],
            'name'     => $report['name'],
            'type'     => $report['type'],
            'data'     => $this->transformForWidget($result['data'], $report['type']),
            'metadata' => $result['metadata'],
        ];
    }

    /**
     * Transform data based on widget type
     * 
     * @param array<int, array<string, mixed>> $data
     * @param string $type
     * @return array<int|string, mixed>
     */
    private function transformForWidget(array $data, string $type): array
    {
        return match ($type) {
            'chart' => $this->transformForChart($data),
            'summary' => $this->transformForSummary($data),
            'table' => $this->transformForTable($data),
            default => $data,
        };
    }

    /**
     * Transform data for chart widget
     * 
     * @param array<int, array<string, mixed>> $data
     * @return array{labels: array<string>, datasets: array<int, array<string, mixed>>}
     */
    private function transformForChart(array $data): array
    {
        if (empty($data)) {
            return ['labels' => [], 'datasets' => []];
        }

        $keys = array_keys($data[0]);
        $labelKey = $keys[0] ?? 'label';
        $valueKeys = array_slice($keys, 1);

        $labels = array_column($data, $labelKey);
        $datasets = [];

        foreach ($valueKeys as $key) {
            $datasets[] = [
                'label' => $key,
                'data'  => array_column($data, $key),
            ];
        }

        return [
            'labels'   => $labels,
            'datasets' => $datasets,
        ];
    }

    /**
     * Transform data for summary widget
     * 
     * @param array<int, array<string, mixed>> $data
     * @return array<string, mixed>
     */
    private function transformForSummary(array $data): array
    {
        if (empty($data)) {
            return [];
        }

        // Return first row for summary widgets
        return $data[0];
    }

    /**
     * Transform data for table widget
     * 
     * @param array<int, array<string, mixed>> $data
     * @return array{columns: array<string>, rows: array<int, array<string, mixed>>}
     */
    private function transformForTable(array $data): array
    {
        if (empty($data)) {
            return ['columns' => [], 'rows' => []];
        }

        return [
            'columns' => array_keys($data[0]),
            'rows'    => $data,
        ];
    }

    /**
     * Optimize dashboard data for mobile consumption
     * 
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function optimizeForMobile(array $data): array
    {
        // Remove unnecessary metadata
        foreach ($data['widgets'] as &$widget) {
            // Limit data rows for mobile
            if (isset($widget['data']['rows']) && is_array($widget['data']['rows'])) {
                $widget['data']['rows'] = array_slice($widget['data']['rows'], 0, 50);
            }
            
            // Simplify metadata
            if (isset($widget['metadata'])) {
                $widget['metadata'] = [
                    'cached' => $widget['metadata']['cached'] ?? false,
                    'count'  => $widget['metadata']['row_count'] ?? 0,
                ];
            }
        }

        // Add compression flag
        if ($this->config->compressMobileResponses) {
            $data['compressed'] = true;
        }

        return $data;
    }

    /**
     * Get summary statistics for dashboard
     * 
     * @param string $tenantId
     * @return array<string, mixed>
     */
    public function getSummaryStats(string $tenantId): array
    {
        $this->reportModel->setTenantId($tenantId);
        
        return [
            'total_reports'  => $this->reportModel->where('is_active', 1)->countAllResults(),
            'public_reports' => $this->reportModel->where('is_public', 1)
                                                  ->where('is_active', 1)
                                                  ->countAllResults(),
            'last_updated'   => $this->getLastUpdatedTime($tenantId),
        ];
    }

    /**
     * Get last updated time for tenant reports
     * 
     * @param string $tenantId
     * @return string|null
     */
    private function getLastUpdatedTime(string $tenantId): ?string
    {
        $this->reportModel->setTenantId($tenantId);
        $latest = $this->reportModel->orderBy('updated_at', 'DESC')->first();
        
        return $latest['updated_at'] ?? null;
    }
}
