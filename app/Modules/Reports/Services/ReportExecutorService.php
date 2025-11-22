<?php

declare(strict_types=1);

namespace Modules\Reports\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\ConnectionInterface;
use Config\Database;
use Modules\Reports\Config\Reports;
use Modules\Reports\Domain\ReportDefinition;
use Modules\Reports\Models\ReportResultModel;

/**
 * Service for executing reports and generating results
 * 
 * Executes report queries, applies filters, aggregations,
 * and manages result caching.
 */
class ReportExecutorService
{
    /**
     * @phpstan-var BaseConnection<object, object>
     */
    private BaseConnection $db;
    private Reports $config;
    private ReportResultModel $resultModel;
    private ReportBuilderService $builder;

    /**
     * @phpstan-param ConnectionInterface<object, object>|null $connection
     */
    public function __construct(?ConnectionInterface $connection = null)
    {
        $this->db = $connection instanceof BaseConnection ? $connection : Database::connect();
        $this->config = config('Reports');
        $this->resultModel = new ReportResultModel();
        $this->builder = new ReportBuilderService();
    }

    /**
     * Execute a report and return results
     * 
     * @param ReportDefinition $definition
     * @param int|null $reportId
     * @param bool $useCache
     * @return array{data: array<int, array<string, mixed>>, metadata: array<string, mixed>}
     */
    public function execute(ReportDefinition $definition, ?int $reportId = null, bool $useCache = true): array
    {
        $filterHash = $this->builder->buildFilterHash($definition->getFilters());

        // Check cache if enabled and report ID provided
        if ($useCache && $reportId && $this->config->enableCaching) {
            $cached = $this->resultModel->getCachedResult($reportId, $filterHash);
            if ($cached) {
                return [
                    'data'     => $cached['result_data'],
                    'metadata' => [
                        'cached'       => true,
                        'generated_at' => $cached['generated_at'],
                        'row_count'    => $cached['row_count'],
                    ],
                ];
            }
        }

        // Execute query
        $results = $this->executeQuery($definition);
        $rowCount = count($results);

        // Cache results if enabled
        if ($reportId && $this->config->enableCaching) {
            $this->cacheResults($reportId, $filterHash, $results, $rowCount);
        }

        return [
            'data'     => $results,
            'metadata' => [
                'cached'       => false,
                'generated_at' => date('Y-m-d H:i:s'),
                'row_count'    => $rowCount,
            ],
        ];
    }

    /**
     * Execute the report query
     * 
     * @param ReportDefinition $definition
     * @return array<int, array<string, mixed>>
     */
    private function executeQuery(ReportDefinition $definition): array
    {
        $builder = $this->buildQuery($definition);
        
        // Apply limit
        if ($definition->getLimit()) {
            $builder->limit($definition->getLimit());
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Build query from report definition
     * 
     * @param ReportDefinition $definition
     * @return \CodeIgniter\Database\BaseBuilder
     */
    private function buildQuery(ReportDefinition $definition): \CodeIgniter\Database\BaseBuilder
    {
        // For this implementation, we'll use a simplified approach
        // In production, you'd map data sources to actual tables
        $table = $this->getTableForDataSource($definition->getDataSource());
        $builder = $this->db->table($table);

        // Select columns
        $columns = $definition->getColumns();
        $aggregations = $definition->getAggregations();

        if (!empty($aggregations)) {
            // Build aggregation query
            $selectParts = [];
            
            foreach ($columns as $column) {
                $selectParts[] = $column;
            }
            
            foreach ($aggregations as $field => $function) {
                $alias = "{$function}_{$field}";
                $selectParts[] = "{$function}({$field}) as {$alias}";
            }
            
            $builder->select(implode(', ', $selectParts));
        } else {
            $builder->select($columns);
        }

        // Apply filters
        $this->applyFilters($builder, $definition->getFilters());

        // Apply grouping
        $groupBy = $definition->getGroupBy();
        if (!empty($groupBy)) {
            foreach ($groupBy as $field) {
                $builder->groupBy($field);
            }
        }

        // Apply ordering
        $orderBy = $definition->getOrderBy();
        if (!empty($orderBy)) {
            foreach ($orderBy as $field => $direction) {
                $builder->orderBy($field, $direction);
            }
        }

        return $builder;
    }

    /**
     * Apply filters to query builder
     * 
     * @param \CodeIgniter\Database\BaseBuilder $builder
     * @param array<int, array{field: string, operator: string, value: mixed}> $filters
     */
    private function applyFilters(\CodeIgniter\Database\BaseBuilder $builder, array $filters): void
    {
        foreach ($filters as $filter) {
            $field = $filter['field'];
            $operator = $filter['operator'] ?? '=';
            $value = $filter['value'];

            match ($operator) {
                '=' => $builder->where($field, $value),
                '!=' => $builder->where($field . ' !=', $value),
                '>' => $builder->where($field . ' >', $value),
                '>=' => $builder->where($field . ' >=', $value),
                '<' => $builder->where($field . ' <', $value),
                '<=' => $builder->where($field . ' <=', $value),
                'LIKE' => $builder->like($field, $value),
                'IN' => $builder->whereIn($field, $value),
                'NOT IN' => $builder->whereNotIn($field, $value),
                'IS NULL' => $builder->where($field, null),
                'IS NOT NULL' => $builder->where($field . ' IS NOT', null),
                default => $builder->where($field, $value),
            };
        }
    }

    /**
     * Get table name for data source
     * 
     * @param string $dataSource
     * @return string
     */
    private function getTableForDataSource(string $dataSource): string
    {
        // Map data sources to actual database tables
        // This is a simplified mapping - in production, use proper table registry
        return match ($dataSource) {
            'finance' => 'ci4_finance_invoices',
            'hr' => 'ci4_hr_employees',
            'inventory' => 'ci4_inventory_items',
            'learning' => 'ci4_learning_courses',
            'library' => 'ci4_library_documents',
            'threads' => 'ci4_threads_threads',
            default => 'ci4_reports_reports',
        };
    }

    /**
     * Cache report results
     * 
     * @param int $reportId
     * @param string $filterHash
     * @param array<int, array<string, mixed>> $results
     * @param int $rowCount
     */
    private function cacheResults(int $reportId, string $filterHash, array $results, int $rowCount): void
    {
        $now = new \DateTime();
        $expiresAt = (clone $now)->modify("+{$this->config->resultCacheTtl} seconds");

        $this->resultModel->insert([
            'report_id'    => $reportId,
            'filter_hash'  => $filterHash,
            'result_data'  => $results,
            'row_count'    => $rowCount,
            'generated_at' => $now->format('Y-m-d H:i:s'),
            'expires_at'   => $expiresAt->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Invalidate cached results for a report
     * 
     * @param int $reportId
     */
    public function invalidateCache(int $reportId): void
    {
        $this->resultModel->cleanForReport($reportId);
    }

    /**
     * Clean expired cached results
     */
    public function cleanExpiredCache(): int
    {
        return $this->resultModel->cleanExpired();
    }
}
