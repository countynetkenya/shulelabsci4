<?php

declare(strict_types=1);

namespace Tests\Ci4\Reports;

use Modules\Reports\Services\ReportMetadataService;

class ReportMetadataServiceTest extends ReportsDatabaseTestCase
{
    private ReportMetadataService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ReportMetadataService();
    }

    public function testGetDataSourcesReturnsEnabledSources(): void
    {
        $sources = $this->service->getDataSources();

        $this->assertIsArray($sources);
        $this->assertNotEmpty($sources);
        
        foreach ($sources as $source) {
            $this->assertTrue($source['enabled']);
            $this->assertArrayHasKey('label', $source);
            $this->assertArrayHasKey('module', $source);
        }
    }

    public function testGetFieldsForModuleReturnsFields(): void
    {
        $fields = $this->service->getFieldsForModule('finance');

        $this->assertIsArray($fields);
        $this->assertNotEmpty($fields);
        
        foreach ($fields as $field) {
            $this->assertArrayHasKey('label', $field);
            $this->assertArrayHasKey('type', $field);
            $this->assertArrayHasKey('aggregatable', $field);
            $this->assertArrayHasKey('filterable', $field);
            $this->assertArrayHasKey('sortable', $field);
        }
    }

    public function testGetAggregatableFieldsReturnsOnlyAggregatableFields(): void
    {
        $fields = $this->service->getAggregatableFields('finance');

        $this->assertIsArray($fields);
        
        foreach ($fields as $field) {
            $this->assertTrue($field['aggregatable']);
        }
    }

    public function testGetFilterableFieldsReturnsOnlyFilterableFields(): void
    {
        $fields = $this->service->getFilterableFields('finance');

        $this->assertIsArray($fields);
        
        foreach ($fields as $field) {
            $this->assertTrue($field['filterable']);
        }
    }

    public function testGetDimensionsReturnsAvailableDimensions(): void
    {
        $dimensions = $this->service->getDimensions();

        $this->assertIsArray($dimensions);
        $this->assertNotEmpty($dimensions);
        $this->assertArrayHasKey('date', $dimensions);
        $this->assertArrayHasKey('month', $dimensions);
    }

    public function testGetPeriodsReturnsAvailablePeriods(): void
    {
        $periods = $this->service->getPeriods();

        $this->assertIsArray($periods);
        $this->assertNotEmpty($periods);
        $this->assertArrayHasKey('today', $periods);
        $this->assertArrayHasKey('this_month', $periods);
    }

    public function testGetAggregationsReturnsAvailableFunctions(): void
    {
        $aggregations = $this->service->getAggregations();

        $this->assertIsArray($aggregations);
        $this->assertNotEmpty($aggregations);
        $this->assertArrayHasKey('sum', $aggregations);
        $this->assertArrayHasKey('count', $aggregations);
    }

    public function testValidateConfigWithValidConfigReturnsValid(): void
    {
        $config = [
            'data_source' => 'finance',
            'columns'     => ['invoice_number', 'invoice_amount'],
        ];

        $result = $this->service->validateConfig($config);

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    public function testValidateConfigWithMissingDataSourceReturnsInvalid(): void
    {
        $config = [
            'columns' => ['invoice_number'],
        ];

        $result = $this->service->validateConfig($config);

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
        $this->assertStringContainsString('Data source', $result['errors'][0]);
    }

    public function testValidateConfigWithMissingColumnsReturnsInvalid(): void
    {
        $config = [
            'data_source' => 'finance',
        ];

        $result = $this->service->validateConfig($config);

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    public function testGetBuilderMetadataReturnsCompleteMetadata(): void
    {
        $metadata = $this->service->getBuilderMetadata();

        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('data_sources', $metadata);
        $this->assertArrayHasKey('dimensions', $metadata);
        $this->assertArrayHasKey('periods', $metadata);
        $this->assertArrayHasKey('aggregations', $metadata);
        $this->assertArrayHasKey('fields', $metadata);
    }
}
