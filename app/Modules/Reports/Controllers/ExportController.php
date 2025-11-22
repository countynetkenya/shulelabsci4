<?php

declare(strict_types=1);

namespace Modules\Reports\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Modules\Reports\Models\ReportModel;
use Modules\Reports\Services\ExportService;
use Modules\Reports\Services\ReportExecutorService;

/**
 * Controller for Export operations
 */
class ExportController extends ResourceController
{
    protected $format = 'json';
    protected ReportModel $reportModel;
    protected ExportService $exportService;
    protected ReportExecutorService $executor;

    public function __construct()
    {
        $this->reportModel = new ReportModel();
        $this->exportService = new ExportService();
        $this->executor = new ReportExecutorService();
    }

    /**
     * Export report to specified format
     */
    public function export($id = null): ResponseInterface
    {
        try {
            $tenantId = $this->request->getGet('tenant_id');
            
            if (!$tenantId) {
                return $this->failValidationErrors('Tenant ID is required');
            }

            if (!$id) {
                return $this->failValidationErrors('Report ID is required');
            }

            $this->reportModel->setTenantId($tenantId);
            $report = $this->reportModel->find($id);

            if (!$report) {
                return $this->failNotFound('Report not found');
            }

            $format = $this->request->getGet('format') ?? 'pdf';
            
            // Validate format
            $allowedFormats = ['pdf', 'excel', 'csv', 'json'];
            if (!in_array($format, $allowedFormats, true)) {
                return $this->failValidationErrors('Invalid export format');
            }

            // Get report data
            $config = $report['config_json'] ?? [];
            $definition = \Modules\Reports\Domain\ReportDefinition::fromArray($config);
            $result = $this->executor->execute($definition, (int) $id, true);

            // Export to requested format
            $exportOptions = [
                'title' => $report['name'],
                'description' => $report['description'] ?? '',
            ];

            $export = $this->exportService->export($result['data'], $format, $exportOptions);

            // Return file download response
            return $this->response
                ->setHeader('Content-Type', $export['mime_type'])
                ->setHeader('Content-Disposition', 'attachment; filename="' . $export['filename'] . '"')
                ->setBody($export['content']);
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * Export report with custom filters
     */
    public function exportWithFilters($id = null): ResponseInterface
    {
        try {
            $tenantId = $this->request->getGet('tenant_id');
            
            if (!$tenantId) {
                return $this->failValidationErrors('Tenant ID is required');
            }

            if (!$id) {
                return $this->failValidationErrors('Report ID is required');
            }

            $this->reportModel->setTenantId($tenantId);
            $report = $this->reportModel->find($id);

            if (!$report) {
                return $this->failNotFound('Report not found');
            }

            $format = $this->request->getGet('format') ?? 'pdf';
            $customFilters = $this->request->getJSON(true) ?? [];

            // Validate format
            $allowedFormats = ['pdf', 'excel', 'csv', 'json'];
            if (!in_array($format, $allowedFormats, true)) {
                return $this->failValidationErrors('Invalid export format');
            }

            // Merge filters with report config
            $config = $report['config_json'] ?? [];
            if (!empty($customFilters)) {
                $config['filters'] = array_merge($config['filters'] ?? [], $customFilters);
            }

            $definition = \Modules\Reports\Domain\ReportDefinition::fromArray($config);
            
            // Execute without caching for custom filters
            $result = $this->executor->execute($definition, null, false);

            // Export to requested format
            $exportOptions = [
                'title' => $report['name'],
                'description' => $report['description'] ?? '',
            ];

            $export = $this->exportService->export($result['data'], $format, $exportOptions);

            // Return file download response
            return $this->response
                ->setHeader('Content-Type', $export['mime_type'])
                ->setHeader('Content-Disposition', 'attachment; filename="' . $export['filename'] . '"')
                ->setBody($export['content']);
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * Get list of supported export formats
     */
    public function formats(): ResponseInterface
    {
        try {
            $formats = [
                'pdf' => [
                    'label' => 'PDF',
                    'mime_type' => 'application/pdf',
                    'extension' => 'pdf',
                ],
                'excel' => [
                    'label' => 'Excel',
                    'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'extension' => 'xlsx',
                ],
                'csv' => [
                    'label' => 'CSV',
                    'mime_type' => 'text/csv',
                    'extension' => 'csv',
                ],
                'json' => [
                    'label' => 'JSON',
                    'mime_type' => 'application/json',
                    'extension' => 'json',
                ],
            ];

            return $this->respond([
                'status' => 'success',
                'data'   => $formats,
            ]);
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }
}
