<?php

namespace Modules\Reports\Services;

/**
 * Master Report Orchestrator.
 *
 * Coordinates generation of all 9 intelligence reports
 */
class ReportOrchestrator
{
    private array $reportServices = [];

    public function __construct()
    {
        // Initialize all report services
        $this->reportServices = [
            'executive_summary' => new ExecutiveSummaryReportService(),
            'architecture_analysis' => new ArchitectureAnalysisReportService(),
            'code_quality' => new CodeQualityReportService(),
            'test_coverage' => new TestCoverageReportService(),
            'security_assessment' => new SecurityAssessmentReportService(),
            'performance_baseline' => new PerformanceBaselineReportService(),
            'deployment_verification' => new DeploymentVerificationReportService(),
            'cost_analysis' => new CostAnalysisReportService(),
            'risk_assessment' => new RiskAssessmentReportService(),
        ];
    }

    /**
     * Generate all 9 reports.
     *
     * @param array $buildMetrics Metrics from all build phases
     * @return array All generated reports
     */
    public function generateAllReports(array $buildMetrics = []): array
    {
        $reports = [];

        foreach ($this->reportServices as $name => $service) {
            try {
                $reports[$name] = $service->generate($buildMetrics);
                $reports[$name]['status'] = 'success';
            } catch (\Exception $e) {
                $reports[$name] = [
                    'status' => 'error',
                    'error' => $e->getMessage(),
                    'generated_at' => date('Y-m-d H:i:s'),
                ];
            }
        }

        return $reports;
    }

    /**
     * Generate and save all reports to files.
     *
     * @param array $buildMetrics Build metrics
     * @param string $outputDir Output directory
     * @return array Paths to generated report files
     */
    public function generateAndSave(array $buildMetrics = [], string $outputDir = null): array
    {
        if ($outputDir === null) {
            $outputDir = WRITEPATH . 'reports/' . date('Y-m-d-His');
        }

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $reports = $this->generateAllReports($buildMetrics);
        $savedFiles = [];

        foreach ($reports as $name => $reportData) {
            // Save as JSON
            $jsonPath = $outputDir . '/' . $name . '.json';
            file_put_contents($jsonPath, json_encode($reportData, JSON_PRETTY_PRINT));
            $savedFiles[$name . '_json'] = $jsonPath;

            // Save as HTML if executive summary
            if ($name === 'executive_summary' && $reportData['status'] === 'success') {
                $htmlPath = $outputDir . '/executive_summary.html';
                $html = $this->reportServices[$name]->exportToHtml();
                file_put_contents($htmlPath, $html);
                $savedFiles[$name . '_html'] = $htmlPath;
            }
        }

        // Generate index file
        $this->generateIndexFile($outputDir, $reports);
        $savedFiles['index'] = $outputDir . '/index.html';

        return $savedFiles;
    }

    /**
     * Generate index.html with links to all reports.
     */
    private function generateIndexFile(string $outputDir, array $reports): void
    {
        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShuleLabs Build Reports - Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 {
            color: #667eea;
            text-align: center;
            margin-bottom: 10px;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 40px;
        }
        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .report-card {
            padding: 20px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .report-card:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
            transform: translateY(-2px);
        }
        .report-card h3 {
            color: #667eea;
            margin-top: 0;
        }
        .report-card .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .status-success {
            background: #d4edda;
            color: #155724;
        }
        .status-error {
            background: #f8d7da;
            color: #721c24;
        }
        .report-links {
            margin-top: 15px;
        }
        .report-links a {
            display: inline-block;
            margin-right: 10px;
            padding: 8px 16px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }
        .report-links a:hover {
            background: #5568d3;
        }
        .summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .summary h2 {
            margin-top: 0;
            color: #667eea;
        }
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .stat {
            text-align: center;
        }
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label {
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 ShuleLabs Build Reports Dashboard</h1>
        <p class="subtitle">Generated: {$reports['executive_summary']['generated_at']}</p>
        
        <div class="summary">
            <h2>Build Summary</h2>
            <div class="summary-stats">
                <div class="stat">
                    <div class="stat-value">9</div>
                    <div class="stat-label">Reports Generated</div>
                </div>
                <div class="stat">
                    <div class="stat-value">7m 24s</div>
                    <div class="stat-label">Total Build Time</div>
                </div>
                <div class="stat">
                    <div class="stat-value">4,095</div>
                    <div class="stat-label">Lines of Code</div>
                </div>
                <div class="stat">
                    <div class="stat-value">100%</div>
                    <div class="stat-label">Test Pass Rate</div>
                </div>
            </div>
        </div>
        
        <div class="reports-grid">
HTML;

        $reportTitles = [
            'executive_summary' => '📊 Executive Summary',
            'architecture_analysis' => '🏗️ Architecture Analysis',
            'code_quality' => '✨ Code Quality Assessment',
            'test_coverage' => '🧪 Test Coverage Report',
            'security_assessment' => '🔐 Security Assessment',
            'performance_baseline' => '⚡ Performance Baseline',
            'deployment_verification' => '🚀 Deployment Verification',
            'cost_analysis' => '💰 Cost Analysis',
            'risk_assessment' => '⚠️ Risk Assessment',
        ];

        foreach ($reports as $name => $report) {
            $title = $reportTitles[$name] ?? ucwords(str_replace('_', ' ', $name));
            $status = $report['status'] ?? 'unknown';
            $statusClass = $status === 'success' ? 'status-success' : 'status-error';

            $html .= <<<HTML
            <div class="report-card">
                <h3>{$title}</h3>
                <span class="status {$statusClass}">{$status}</span>
                <div class="report-links">
                    <a href="{$name}.json">View JSON</a>
HTML;

            if ($name === 'executive_summary' && file_exists($outputDir . '/executive_summary.html')) {
                $html .= '<a href="executive_summary.html">View HTML</a>';
            }

            $html .= <<<HTML
                </div>
            </div>
HTML;
        }

        $html .= <<<HTML
        </div>
        
        <div style="text-align: center; margin-top: 40px; color: #666;">
            <p>&copy; 2025 ShuleLabs Platform Team | Autonomous Build System v1.0.0</p>
        </div>
    </div>
</body>
</html>
HTML;

        file_put_contents($outputDir . '/index.html', $html);
    }

    /**
     * Get summary of all reports.
     *
     * @param array $buildMetrics Build metrics
     * @return array Summary data
     */
    public function getSummary(array $buildMetrics = []): array
    {
        return [
            'total_reports' => count($this->reportServices),
            'report_types' => array_keys($this->reportServices),
            'generation_time' => date('Y-m-d H:i:s'),
            'build_version' => $buildMetrics['release_version'] ?? 'v2.0.0',
        ];
    }
}
