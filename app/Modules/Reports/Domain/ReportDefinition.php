<?php

declare(strict_types=1);

namespace Modules\Reports\Domain;

/**
 * Represents a report definition/configuration
 */
class ReportDefinition
{
    /**
     * @param array<string> $columns
     * @param array<string, mixed> $filters
     * @param array<string, string> $groupBy
     * @param array<string, string> $aggregations
     * @param array<string, string> $orderBy
     * @param array<string, mixed> $options
     */
    public function __construct(
        private string $dataSource,
        private array $columns,
        private array $filters = [],
        private array $groupBy = [],
        private array $aggregations = [],
        private array $orderBy = [],
        private ?int $limit = null,
        private array $options = [],
    ) {
    }

    public function getDataSource(): string
    {
        return $this->dataSource;
    }

    public function setDataSource(string $dataSource): void
    {
        $this->dataSource = $dataSource;
    }

    /**
     * @return array<string>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @param array<string> $columns
     */
    public function setColumns(array $columns): void
    {
        $this->columns = $columns;
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function setFilters(array $filters): void
    {
        $this->filters = $filters;
    }

    public function addFilter(string $field, mixed $value, string $operator = '='): void
    {
        $this->filters[] = [
            'field'    => $field,
            'operator' => $operator,
            'value'    => $value,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function getGroupBy(): array
    {
        return $this->groupBy;
    }

    /**
     * @param array<string, string> $groupBy
     */
    public function setGroupBy(array $groupBy): void
    {
        $this->groupBy = $groupBy;
    }

    /**
     * @return array<string, string>
     */
    public function getAggregations(): array
    {
        return $this->aggregations;
    }

    /**
     * @param array<string, string> $aggregations
     */
    public function setAggregations(array $aggregations): void
    {
        $this->aggregations = $aggregations;
    }

    public function addAggregation(string $field, string $function): void
    {
        $this->aggregations[$field] = $function;
    }

    /**
     * @return array<string, string>
     */
    public function getOrderBy(): array
    {
        return $this->orderBy;
    }

    /**
     * @param array<string, string> $orderBy
     */
    public function setOrderBy(array $orderBy): void
    {
        $this->orderBy = $orderBy;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function setLimit(?int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'data_source'  => $this->dataSource,
            'columns'      => $this->columns,
            'filters'      => $this->filters,
            'group_by'     => $this->groupBy,
            'aggregations' => $this->aggregations,
            'order_by'     => $this->orderBy,
            'limit'        => $this->limit,
            'options'      => $this->options,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            dataSource: $data['data_source'],
            columns: $data['columns'] ?? [],
            filters: $data['filters'] ?? [],
            groupBy: $data['group_by'] ?? [],
            aggregations: $data['aggregations'] ?? [],
            orderBy: $data['order_by'] ?? [],
            limit: $data['limit'] ?? null,
            options: $data['options'] ?? [],
        );
    }
}
