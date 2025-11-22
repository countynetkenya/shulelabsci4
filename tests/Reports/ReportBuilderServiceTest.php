<?php

declare(strict_types=1);

namespace Tests\Ci4\Reports;

use Modules\Reports\Services\ReportBuilderService;
use Modules\Reports\Domain\ReportDefinition;

class ReportBuilderServiceTest extends ReportsDatabaseTestCase
{
    private ReportBuilderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ReportBuilderService();
    }

    public function testBuildDefinitionCreatesDefinition(): void
    {
        $config = [
            'data_source' => 'finance',
            'columns'     => ['invoice_number', 'invoice_amount'],
            'filters'     => [],
            'group_by'    => [],
            'aggregations' => [],
            'order_by'    => [],
        ];

        $definition = $this->service->buildDefinition($config);

        $this->assertInstanceOf(ReportDefinition::class, $definition);
        $this->assertSame('finance', $definition->getDataSource());
        $this->assertSame(['invoice_number', 'invoice_amount'], $definition->getColumns());
    }

    public function testValidateAndNormalizeWithValidConfigReturnsNormalized(): void
    {
        $config = [
            'data_source' => 'finance',
            'columns'     => ['invoice_number'],
        ];

        $result = $this->service->validateAndNormalize($config);

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
        $this->assertArrayHasKey('normalized', $result);
        $this->assertArrayHasKey('data_source', $result['normalized']);
        $this->assertArrayHasKey('columns', $result['normalized']);
    }

    public function testValidateAndNormalizeWithInvalidConfigReturnsErrors(): void
    {
        $config = [
            'columns' => ['invoice_number'],
        ];

        $result = $this->service->validateAndNormalize($config);

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
        $this->assertArrayNotHasKey('normalized', $result);
    }

    public function testBuildFilterHashCreatesConsistentHash(): void
    {
        $filters1 = [
            'field1' => 'value1',
            'field2' => 'value2',
        ];

        $filters2 = [
            'field2' => 'value2',
            'field1' => 'value1',
        ];

        $hash1 = $this->service->buildFilterHash($filters1);
        $hash2 = $this->service->buildFilterHash($filters2);

        $this->assertSame($hash1, $hash2);
        $this->assertSame(64, strlen($hash1)); // SHA-256 hash length
    }

    public function testApplyPeriodFilterAddsFilters(): void
    {
        $config = [
            'data_source' => 'finance',
            'columns'     => ['invoice_number'],
        ];

        $result = $this->service->applyPeriodFilter($config, 'today');

        $this->assertArrayHasKey('filters', $result);
        $this->assertNotEmpty($result['filters']);
        $this->assertCount(2, $result['filters']); // Start and end date
    }

    public function testApplyPeriodFilterWithCustomDates(): void
    {
        $config = [
            'data_source' => 'finance',
            'columns'     => ['invoice_number'],
        ];

        $result = $this->service->applyPeriodFilter(
            $config,
            'custom',
            '2024-01-01 00:00:00',
            '2024-12-31 23:59:59'
        );

        $this->assertArrayHasKey('filters', $result);
        $this->assertCount(2, $result['filters']);
        
        $this->assertSame('2024-01-01 00:00:00', $result['filters'][0]['value']);
        $this->assertSame('2024-12-31 23:59:59', $result['filters'][1]['value']);
    }

    public function testApplyPeriodFilterWithInvalidPeriodReturnsOriginalConfig(): void
    {
        $config = [
            'data_source' => 'finance',
            'columns'     => ['invoice_number'],
        ];

        $result = $this->service->applyPeriodFilter($config, 'invalid_period');

        $this->assertSame($config, $result);
    }
}
