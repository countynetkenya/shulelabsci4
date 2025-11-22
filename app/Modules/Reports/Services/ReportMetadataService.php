<?php

declare(strict_types=1);

namespace Modules\Reports\Services;

use Modules\Reports\Config\ReportFields;
use Modules\Reports\Config\Reports;

/**
 * Service for discovering and managing report metadata
 * 
 * Provides dynamic field discovery and metadata management
 * for the reporting engine.
 */
class ReportMetadataService
{
    private Reports $config;
    private ReportFields $fieldsConfig;

    public function __construct()
    {
        $this->config = config('Reports');
        $this->fieldsConfig = config('ReportFields');
    }

    /**
     * Get available data sources
     * 
     * @return array<string, array{label: string, module: string, enabled: bool}>
     */
    public function getDataSources(): array
    {
        return array_filter(
            $this->config->dataSources,
            fn($source) => $source['enabled']
        );
    }

    /**
     * Get fields for a specific module
     * 
     * @param string $module
     * @return array<string, array{label: string, type: string, aggregatable: bool, filterable: bool, sortable: bool, table?: string, alias?: string}>
     */
    public function getFieldsForModule(string $module): array
    {
        return $this->fieldsConfig->getFieldsForModule($module);
    }

    /**
     * Get aggregatable fields for a module
     * 
     * @param string $module
     * @return array<string, array{label: string, type: string, aggregatable: bool, filterable: bool, sortable: bool, table?: string, alias?: string}>
     */
    public function getAggregatableFields(string $module): array
    {
        return $this->fieldsConfig->getAggregatableFields($module);
    }

    /**
     * Get filterable fields for a module
     * 
     * @param string $module
     * @return array<string, array{label: string, type: string, aggregatable: bool, filterable: bool, sortable: bool, table?: string, alias?: string}>
     */
    public function getFilterableFields(string $module): array
    {
        return $this->fieldsConfig->getFilterableFields($module);
    }

    /**
     * Get available dimensions
     * 
     * @return array<string, array{label: string, field: string, type: string}>
     */
    public function getDimensions(): array
    {
        return $this->config->dimensions;
    }

    /**
     * Get available time periods
     * 
     * @return array<string, array{label: string, days: int}>
     */
    public function getPeriods(): array
    {
        return $this->config->periods;
    }

    /**
     * Get available aggregation functions
     * 
     * @return array<string, string>
     */
    public function getAggregations(): array
    {
        return $this->config->aggregations;
    }

    /**
     * Validate report configuration
     * 
     * @param array<string, mixed> $config
     * @return array{valid: bool, errors: array<string>}
     */
    public function validateConfig(array $config): array
    {
        $errors = [];

        // Validate data source
        if (!isset($config['data_source'])) {
            $errors[] = 'Data source is required';
        } elseif (!$this->config->isDataSourceEnabled($config['data_source'])) {
            $errors[] = 'Invalid or disabled data source';
        }

        // Validate columns
        if (!isset($config['columns']) || empty($config['columns'])) {
            $errors[] = 'At least one column is required';
        }

        // Validate aggregations if present
        if (isset($config['aggregations']) && is_array($config['aggregations'])) {
            foreach ($config['aggregations'] as $field => $function) {
                if (!isset($this->config->aggregations[$function])) {
                    $errors[] = "Invalid aggregation function: {$function}";
                }
            }
        }

        return [
            'valid'  => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Get complete metadata for report builder UI
     * 
     * @return array<string, mixed>
     */
    public function getBuilderMetadata(): array
    {
        return [
            'data_sources' => $this->getDataSources(),
            'dimensions'   => $this->getDimensions(),
            'periods'      => $this->getPeriods(),
            'aggregations' => $this->getAggregations(),
            'fields'       => $this->fieldsConfig->fields,
        ];
    }
}
