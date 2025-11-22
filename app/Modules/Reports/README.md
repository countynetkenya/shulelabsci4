# 📊 Reports Module - Autonomous Intelligence Reports

**Version**: 1.0.0  
**Module**: Reports  
**Status**: Production Ready

## Overview

The Reports Module is the intelligence layer of the ShuleLabs CI4 autonomous system orchestration. It automatically generates 9 comprehensive reports covering all aspects of the build, deployment, and system health.

## Features

### 9 Intelligence Reports

1. **Executive Summary** - High-level overview for stakeholders
2. **Architecture Analysis** - Module structure and design patterns
3. **Code Quality Assessment** - Complexity metrics and technical debt
4. **Test Coverage Report** - Test execution and coverage details
5. **Security Assessment** - Vulnerability scan and compliance
6. **Performance Baseline** - Response times and resource utilization
7. **Deployment Verification** - Deployment timeline and health
8. **Cost Analysis** - Infrastructure costs and ROI
9. **Risk Assessment** - Risk identification and mitigation

## Installation

The Reports Module is included in the ShuleLabs CI4 installation. No additional setup required.

## Usage

### Generate All Reports via API

```bash
curl -X POST https://api.shulelabs.com/api/v1/reports/generate \
  -H "Content-Type: application/json" \
  -d '{
    "release_version": "v2.0.0",
    "total_tests": 192,
    "passing_tests": 192,
    "code_coverage": 85.5
  }'
```

### Get Reports Summary

```bash
curl https://api.shulelabs.com/api/v1/reports/summary
```

### Programmatic Usage

```php
<?php

use Modules\Reports\Services\ReportOrchestrator;

// Initialize orchestrator
$orchestrator = new ReportOrchestrator();

// Generate all reports with metrics
$buildMetrics = [
    'release_version' => 'v2.0.0',
    'execution_time' => '7m 24s',
    'total_tests' => 192,
    'passing_tests' => 192,
    'code_coverage' => 85.5,
];

$reports = $orchestrator->generateAllReports($buildMetrics);

// Save to files
$savedFiles = $orchestrator->generateAndSave($buildMetrics, '/path/to/output');

// Get summary
$summary = $orchestrator->getSummary($buildMetrics);
```

## Report Formats

### JSON Format

All reports are available in JSON format for programmatic access:

```json
{
  "report_title": "Executive Summary Report",
  "generated_at": "2025-11-22 13:20:00",
  "build_overview": {
    "status": "completed",
    "total_execution_time": "7m 24s",
    "phases_completed": 6
  },
  "key_metrics": {
    "code_generated": {
      "lines": 4095,
      "files": 87
    }
  }
}
```

### HTML Format

Executive Summary is also available in rich HTML format with:
- Responsive design
- Interactive metrics
- Color-coded status indicators
- Print-friendly layout

## Directory Structure

```
app/Modules/Reports/
├── Controllers/
│   └── ReportsController.php
├── Services/
│   ├── ExecutiveSummaryReportService.php
│   ├── ArchitectureAnalysisReportService.php
│   ├── CodeQualityReportService.php
│   ├── TestCoverageReportService.php
│   ├── SecurityAssessmentReportService.php
│   ├── PerformanceBaselineReportService.php
│   ├── DeploymentVerificationReportService.php
│   ├── CostAnalysisReportService.php
│   ├── RiskAssessmentReportService.php
│   └── ReportOrchestrator.php
├── Config/
│   └── Orchestration.php
└── README.md
```

## Generated Output

Reports are saved to:
```
writable/reports/YYYY-MM-DD-HHmmss/
├── index.html (Dashboard with all reports)
├── executive_summary.json
├── executive_summary.html
├── architecture_analysis.json
├── code_quality.json
├── test_coverage.json
├── security_assessment.json
├── performance_baseline.json
├── deployment_verification.json
├── cost_analysis.json
└── risk_assessment.json
```

## API Endpoints

### POST /api/v1/reports/generate
Generate all 9 reports and save to files

**Request Body**:
```json
{
  "release_version": "v2.0.0",
  "total_tests": 192,
  "passing_tests": 192,
  "code_coverage": 85.5
}
```

**Response**:
```json
{
  "status": "success",
  "message": "All 9 reports generated successfully",
  "data": {
    "total_reports": 9,
    "files": {
      "executive_summary_json": "/path/to/executive_summary.json",
      "executive_summary_html": "/path/to/executive_summary.html"
    }
  }
}
```

### GET /api/v1/reports
Get all reports data (in-memory, not saved)

**Response**:
```json
{
  "status": "success",
  "data": {
    "executive_summary": { ... },
    "architecture_analysis": { ... },
    ...
  }
}
```

### GET /api/v1/reports/summary
Get reports summary

**Response**:
```json
{
  "status": "success",
  "data": {
    "total_reports": 9,
    "report_types": ["executive_summary", "architecture_analysis", ...],
    "generation_time": "2025-11-22 13:20:00"
  }
}
```

## Configuration

Configure orchestration in `app/Modules/Reports/Config/Orchestration.php`:

```php
public array $reports = [
    'generate_pdf' => true,
    'generate_html' => true,
    'publish_to_dashboard' => true,
    'email_reports' => true,
];
```

## Integration with Master Orchestration

The Reports Module is automatically invoked during Phase 6 of the Master Orchestration:

```
Phase 6: REPORTS (5 minutes)
├── Execute all 9 report generators
├── Collect metrics from all phases
├── Generate visual dashboards
└── Publish and notify stakeholders
```

## Customization

### Adding Custom Reports

1. Create a new service in `Services/`:

```php
<?php

namespace Modules\Reports\Services;

class CustomReportService
{
    public function generate(array $metrics = []): array
    {
        return [
            'report_title' => 'Custom Report',
            'generated_at' => date('Y-m-d H:i:s'),
            // Your custom data
        ];
    }
}
```

2. Register in `ReportOrchestrator.php`:

```php
$this->reportServices['custom_report'] = new CustomReportService();
```

### Customizing Report Templates

Modify the `exportToHtml()` method in any report service to customize the HTML output.

## Metrics Tracking

Reports collect and analyze metrics from:
- **Phase 1**: Backup size, verification status
- **Phase 2**: Lines generated, files created
- **Phase 3**: Test results, code coverage, quality scores
- **Phase 4**: Merge status, conflicts resolved
- **Phase 5**: Deployment times, health checks
- **Phase 6**: Report generation times

## Best Practices

1. **Regular Generation**: Generate reports after every build
2. **Archive Reports**: Keep historical reports for trend analysis
3. **Review Metrics**: Regularly review key metrics and trends
4. **Act on Risks**: Address identified risks promptly
5. **Share with Stakeholders**: Distribute reports to relevant teams

## Troubleshooting

### Reports Not Generating

Check:
- `writable/reports/` directory permissions (755)
- Disk space availability
- PHP memory limit (512M recommended)

### Missing Metrics

Ensure build metrics are passed to report generators:
```php
$metrics = [
    'total_tests' => 192,
    'passing_tests' => 192,
    // ... other metrics
];
```

### HTML Report Not Rendering

Check:
- Browser JavaScript enabled
- CSS loaded correctly
- No console errors

## Performance

- **Generation Time**: ~5 minutes for all 9 reports
- **Memory Usage**: ~128MB peak
- **Disk Usage**: ~2-5MB per report set
- **Concurrent Generation**: Supported

## Security

- Reports may contain sensitive data
- Restrict access to authorized users only
- Use HTTPS for report transmission
- Consider encrypting archived reports

## Support

For issues or questions:
- Check documentation: `/docs/agents/master-orchestration-agent.md`
- Contact: platform-team@shulelabs.com
- GitHub Issues: https://github.com/countynetkenya/shulelabsci4/issues

## License

Part of ShuleLabs CI4 Platform - Proprietary

---

**Maintained By**: ShuleLabs Platform Team  
**Last Updated**: 2025-11-22  
**Version**: 1.0.0
