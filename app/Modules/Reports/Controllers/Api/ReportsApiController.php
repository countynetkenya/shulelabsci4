<?php

namespace Modules\Reports\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Modules\Reports\Services\ReportOrchestrator;

/**
 * Reports Controller.
 *
 * Handles report generation and retrieval endpoints
 */
class ReportsApiController extends ResourceController
{
    protected $modelName = null;

    protected $format = 'json';

    /**
     * Generate all reports.
     *
     * POST /api/v1/reports/generate
     */
    public function generate()
    {
        try {
            $buildMetrics = $this->request->getJSON(true) ?? [];

            $orchestrator = new ReportOrchestrator();
            $savedFiles = $orchestrator->generateAndSave($buildMetrics);

            return $this->respond([
                'status' => 'success',
                'message' => 'All 9 reports generated successfully',
                'data' => [
                    'total_reports' => 9,
                    'files' => $savedFiles,
                    'generated_at' => date('Y-m-d H:i:s'),
                ],
            ], 201);

        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => 'Failed to generate reports',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all reports data.
     *
     * GET /api/v1/reports
     */
    public function index()
    {
        try {
            $buildMetrics = $this->request->getGet() ?? [];

            $orchestrator = new ReportOrchestrator();
            $reports = $orchestrator->generateAllReports($buildMetrics);

            return $this->respond([
                'status' => 'success',
                'data' => $reports,
            ]);

        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => 'Failed to retrieve reports',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get summary of reports.
     *
     * GET /api/v1/reports/summary
     */
    public function summary()
    {
        try {
            $orchestrator = new ReportOrchestrator();
            $summary = $orchestrator->getSummary();

            return $this->respond([
                'status' => 'success',
                'data' => $summary,
            ]);

        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => 'Failed to get summary',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
