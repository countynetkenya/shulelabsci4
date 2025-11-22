<?php

declare(strict_types=1);

namespace Tests\Ci4\Reports;

use Modules\Reports\Services\ExportService;

class ExportServiceTest extends ReportsDatabaseTestCase
{
    private ExportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ExportService();
    }

    public function testExportToCsvGeneratesValidCsv(): void
    {
        $data = [
            ['name' => 'John', 'age' => 30, 'city' => 'Nairobi'],
            ['name' => 'Jane', 'age' => 25, 'city' => 'Mombasa'],
        ];

        $result = $this->service->export($data, 'csv', ['title' => 'Test Report']);

        $this->assertArrayHasKey('content', $result);
        $this->assertArrayHasKey('filename', $result);
        $this->assertArrayHasKey('mime_type', $result);
        
        $this->assertSame('text/csv', $result['mime_type']);
        $this->assertStringContainsString('.csv', $result['filename']);
        $this->assertStringContainsString('name', $result['content']);
        $this->assertStringContainsString('John', $result['content']);
    }

    public function testExportToJsonGeneratesValidJson(): void
    {
        $data = [
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
        ];

        $result = $this->service->export($data, 'json', ['title' => 'Test Report']);

        $this->assertArrayHasKey('content', $result);
        $this->assertArrayHasKey('filename', $result);
        $this->assertArrayHasKey('mime_type', $result);
        
        $this->assertSame('application/json', $result['mime_type']);
        $this->assertStringContainsString('.json', $result['filename']);
        
        $decoded = json_decode($result['content'], true);
        $this->assertIsArray($decoded);
        $this->assertCount(2, $decoded);
    }

    public function testExportToPdfGeneratesHtml(): void
    {
        $data = [
            ['name' => 'John', 'age' => 30],
        ];

        $result = $this->service->export($data, 'pdf', ['title' => 'Test Report']);

        $this->assertArrayHasKey('content', $result);
        $this->assertArrayHasKey('filename', $result);
        $this->assertArrayHasKey('mime_type', $result);
        
        $this->assertSame('application/pdf', $result['mime_type']);
        $this->assertStringContainsString('.pdf', $result['filename']);
        $this->assertStringContainsString('<table', $result['content']);
        $this->assertStringContainsString('Test Report', $result['content']);
    }

    public function testExportToExcelGeneratesCsv(): void
    {
        $data = [
            ['name' => 'John', 'age' => 30],
        ];

        $result = $this->service->export($data, 'excel', ['title' => 'Test Report']);

        $this->assertArrayHasKey('content', $result);
        $this->assertArrayHasKey('filename', $result);
        $this->assertArrayHasKey('mime_type', $result);
        
        $this->assertSame('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $result['mime_type']);
        $this->assertStringContainsString('.xlsx', $result['filename']);
    }

    public function testExportWithUnsupportedFormatThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unsupported export format');

        $data = [['name' => 'John']];
        $this->service->export($data, 'invalid_format');
    }

    public function testExportWithEmptyDataReturnsEmptyContent(): void
    {
        $data = [];

        $result = $this->service->export($data, 'csv');

        $this->assertArrayHasKey('content', $result);
        $this->assertSame('', $result['content']);
    }

    public function testFilenameGenerationIncludesTimestamp(): void
    {
        $data = [['name' => 'John']];

        $result1 = $this->service->export($data, 'csv', ['title' => 'Test Report']);
        
        // Small delay to ensure different timestamp
        usleep(1100000);
        
        $result2 = $this->service->export($data, 'csv', ['title' => 'Test Report']);

        $this->assertNotSame($result1['filename'], $result2['filename']);
    }
}
