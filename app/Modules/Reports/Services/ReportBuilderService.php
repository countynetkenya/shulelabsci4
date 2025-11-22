<?php

declare(strict_types=1);

namespace Modules\Reports\Services;

use Modules\Reports\Domain\ReportDefinition;
use Modules\Reports\Config\Reports;

/**
 * Service for building report configurations
 * 
 * Builds reports from metadata and provides builder interface support.
 */
class ReportBuilderService
{
    private Reports $config;
    private ReportMetadataService $metadataService;

    public function __construct()
    {
        $this->config = config('Reports');
        $this->metadataService = new ReportMetadataService();
    }

    /**
     * Build a report definition from configuration
     * 
     * @param array<string, mixed> $config
     * @return ReportDefinition
     */
    public function buildDefinition(array $config): ReportDefinition
    {
        return ReportDefinition::fromArray($config);
    }

    /**
     * Validate and normalize report configuration
     * 
     * @param array<string, mixed> $config
     * @return array{valid: bool, errors: array<string>, normalized?: array<string, mixed>}
     */
    public function validateAndNormalize(array $config): array
    {
        $validation = $this->metadataService->validateConfig($config);
        
        if (!$validation['valid']) {
            return $validation;
        }

        $normalized = $this->normalizeConfig($config);

        return [
            'valid'      => true,
            'errors'     => [],
            'normalized' => $normalized,
        ];
    }

    /**
     * Normalize report configuration
     * 
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    private function normalizeConfig(array $config): array
    {
        $normalized = [
            'data_source'  => $config['data_source'],
            'columns'      => $config['columns'] ?? [],
            'filters'      => $config['filters'] ?? [],
            'group_by'     => $config['group_by'] ?? [],
            'aggregations' => $config['aggregations'] ?? [],
            'order_by'     => $config['order_by'] ?? [],
            'limit'        => $config['limit'] ?? $this->config->maxReportRows,
            'options'      => $config['options'] ?? [],
        ];

        // Apply max limit
        if ($normalized['limit'] > $this->config->maxReportRows) {
            $normalized['limit'] = $this->config->maxReportRows;
        }

        return $normalized;
    }

    /**
     * Build filter hash for caching
     * 
     * @param array<string, mixed> $filters
     * @return string
     */
    public function buildFilterHash(array $filters): string
    {
        ksort($filters);
        return hash('sha256', json_encode($filters));
    }

    /**
     * Apply date range filter based on period
     * 
     * @param array<string, mixed> $config
     * @param string $period
     * @param string|null $customStart
     * @param string|null $customEnd
     * @return array<string, mixed>
     */
    public function applyPeriodFilter(
        array $config,
        string $period,
        ?string $customStart = null,
        ?string $customEnd = null
    ): array {
        $periodConfig = $this->config->getPeriod($period);
        
        if (!$periodConfig) {
            return $config;
        }

        $dateField = $config['date_field'] ?? 'created_at';
        $filters = $config['filters'] ?? [];

        if ($period === 'custom' && $customStart && $customEnd) {
            $filters[] = [
                'field'    => $dateField,
                'operator' => '>=',
                'value'    => $customStart,
            ];
            $filters[] = [
                'field'    => $dateField,
                'operator' => '<=',
                'value'    => $customEnd,
            ];
        } else {
            $dateRange = $this->calculateDateRange($period);
            if ($dateRange) {
                $filters[] = [
                    'field'    => $dateField,
                    'operator' => '>=',
                    'value'    => $dateRange['start'],
                ];
                $filters[] = [
                    'field'    => $dateField,
                    'operator' => '<=',
                    'value'    => $dateRange['end'],
                ];
            }
        }

        $config['filters'] = $filters;
        return $config;
    }

    /**
     * Calculate date range for a period
     * 
     * @param string $period
     * @return array{start: string, end: string}|null
     */
    private function calculateDateRange(string $period): ?array
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone($this->config->timezone));
        
        return match ($period) {
            'today' => [
                'start' => $now->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
                'end'   => $now->setTime(23, 59, 59)->format('Y-m-d H:i:s'),
            ],
            'yesterday' => [
                'start' => $now->modify('-1 day')->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
                'end'   => $now->modify('-1 day')->setTime(23, 59, 59)->format('Y-m-d H:i:s'),
            ],
            'this_week' => [
                'start' => $now->modify('monday this week')->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
                'end'   => $now->setTime(23, 59, 59)->format('Y-m-d H:i:s'),
            ],
            'last_week' => [
                'start' => $now->modify('monday last week')->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
                'end'   => $now->modify('sunday last week')->setTime(23, 59, 59)->format('Y-m-d H:i:s'),
            ],
            'this_month' => [
                'start' => $now->modify('first day of this month')->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
                'end'   => $now->setTime(23, 59, 59)->format('Y-m-d H:i:s'),
            ],
            'last_month' => [
                'start' => $now->modify('first day of last month')->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
                'end'   => $now->modify('last day of last month')->setTime(23, 59, 59)->format('Y-m-d H:i:s'),
            ],
            'this_quarter' => $this->getCurrentQuarter($now),
            'last_quarter' => $this->getLastQuarter($now),
            'this_year' => [
                'start' => $now->modify('first day of January this year')->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
                'end'   => $now->setTime(23, 59, 59)->format('Y-m-d H:i:s'),
            ],
            'last_year' => [
                'start' => $now->modify('first day of January last year')->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
                'end'   => $now->modify('last day of December last year')->setTime(23, 59, 59)->format('Y-m-d H:i:s'),
            ],
            default => null,
        };
    }

    /**
     * @param \DateTimeImmutable $date
     * @return array{start: string, end: string}
     */
    private function getCurrentQuarter(\DateTimeImmutable $date): array
    {
        $month = (int) $date->format('n');
        $quarter = (int) ceil($month / 3);
        $startMonth = ($quarter - 1) * 3 + 1;
        
        $start = $date->setDate((int) $date->format('Y'), $startMonth, 1)->setTime(0, 0, 0);
        $end = $date->setTime(23, 59, 59);

        return [
            'start' => $start->format('Y-m-d H:i:s'),
            'end'   => $end->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @param \DateTimeImmutable $date
     * @return array{start: string, end: string}
     */
    private function getLastQuarter(\DateTimeImmutable $date): array
    {
        $month = (int) $date->format('n');
        $quarter = (int) ceil($month / 3);
        $lastQuarter = $quarter === 1 ? 4 : $quarter - 1;
        $year = $quarter === 1 ? (int) $date->format('Y') - 1 : (int) $date->format('Y');
        
        $startMonth = ($lastQuarter - 1) * 3 + 1;
        $endMonth = $startMonth + 2;
        
        $start = $date->setDate($year, $startMonth, 1)->setTime(0, 0, 0);
        $end = $date->setDate($year, $endMonth, (int) $start->modify("last day of +2 months")->format('d'))
                    ->setTime(23, 59, 59);

        return [
            'start' => $start->format('Y-m-d H:i:s'),
            'end'   => $end->format('Y-m-d H:i:s'),
        ];
    }
}
