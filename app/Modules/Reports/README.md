# Reports Module

A comprehensive QuickBooks-style reporting engine for ShuleLabs CI4 with metadata-driven report generation, multi-tenant support, and mobile-first API design.

## Features

- **Metadata-Driven Reporting**: Build reports dynamically from configuration
- **Multi-Tenant Support**: Complete tenant isolation using TenantAwareModel
- **Report Builder**: Drag-and-drop interface for creating custom reports
- **Multiple Export Formats**: PDF, Excel, CSV, and JSON exports
- **Dashboard API**: Mobile-optimized dashboard and widget endpoints
- **Report Templates**: Reusable report templates for common use cases
- **Caching**: Result caching for improved performance
- **Scheduled Reports**: Automated report generation and distribution
- **Audit Trail**: Integration with audit service for report access tracking

## Architecture

### Module Structure

```
Reports/
├── Config/
│   ├── Reports.php          # Main configuration
│   ├── ReportFields.php     # Reportable fields mapping
│   └── Routes.php           # Route definitions
├── Controllers/
│   ├── ReportController.php      # CRUD operations
│   ├── BuilderController.php     # Builder UI endpoints
│   ├── DashboardController.php   # Dashboard API
│   └── ExportController.php      # Export operations
├── Database/
│   └── Migrations/
│       └── 2024-11-22-000001_CreateReportsTables.php
├── Domain/
│   ├── Report.php               # Report entity
│   ├── ReportTemplate.php       # Template entity
│   └── ReportDefinition.php     # Definition entity
├── Models/
│   ├── ReportModel.php          # Report model
│   ├── ReportTemplateModel.php  # Template model
│   ├── ReportFilterModel.php    # Filter model
│   ├── ReportResultModel.php    # Cached results
│   └── ReportScheduleModel.php  # Schedules
├── Services/
│   ├── ReportBuilderService.php    # Build reports
│   ├── ReportExecutorService.php   # Execute queries
│   ├── ExportService.php           # Handle exports
│   ├── ReportMetadataService.php   # Field discovery
│   └── DashboardApiService.php     # Dashboard data
└── Views/
    ├── builder.php    # Builder UI
    ├── viewer.php     # Report viewer
    └── dashboard.php  # Dashboard UI
```

## Database Schema

### Tables

1. **ci4_reports_reports**: Main reports table
   - Multi-tenant with tenant_id
   - Stores report configuration as JSON
   - Supports public/private reports

2. **ci4_reports_templates**: Reusable report templates
   - System and custom templates
   - Categorized by module

3. **ci4_reports_filters**: Saved filter configurations
   - Linked to reports
   - Supports default filters

4. **ci4_reports_results**: Cached report results
   - Hash-based cache keys
   - TTL-based expiration

5. **ci4_reports_schedules**: Scheduled report executions
   - Frequency-based scheduling
   - Multiple export formats

## API Endpoints

### Reports CRUD

```
GET    /api/reports                    # List reports
POST   /api/reports                    # Create report
GET    /api/reports/{id}               # Get report
PUT    /api/reports/{id}               # Update report
DELETE /api/reports/{id}               # Delete report
GET    /api/reports/{id}/execute       # Execute report
```

### Builder

```
GET    /api/reports/builder/metadata        # Get builder metadata
GET    /api/reports/builder/data-sources    # Get data sources
GET    /api/reports/builder/fields/{module} # Get module fields
GET    /api/reports/builder/templates       # Get templates
POST   /api/reports/builder/validate        # Validate config
POST   /api/reports/builder/preview         # Preview report
```

### Dashboard

```
GET    /api/reports/dashboard              # Get dashboard data
GET    /api/reports/dashboard/stats        # Get summary stats
GET    /api/reports/dashboard/widget/{id}  # Get widget data
POST   /api/reports/dashboard/widget/{id}  # Get widget with filters
```

### Export

```
GET    /api/reports/{id}/export              # Export report
POST   /api/reports/{id}/export              # Export with filters
GET    /api/reports/export/formats           # Get supported formats
```

## Usage Examples

### Creating a Report

```php
use Modules\Reports\Models\ReportModel;

$reportModel = new ReportModel();
$reportModel->setTenantId('tenant-123');

$report = $reportModel->insert([
    'name' => 'Monthly Revenue Report',
    'type' => 'table',
    'config_json' => [
        'data_source' => 'finance',
        'columns' => ['invoice_number', 'invoice_amount', 'payment_date'],
        'filters' => [
            ['field' => 'created_at', 'operator' => '>=', 'value' => '2024-01-01']
        ],
        'order_by' => ['created_at' => 'DESC'],
    ],
    'owner_id' => 'user-456',
    'tenant_id' => 'tenant-123',
    'is_public' => false,
]);
```

### Executing a Report

```php
use Modules\Reports\Services\ReportExecutorService;
use Modules\Reports\Domain\ReportDefinition;

$executor = new ReportExecutorService();
$definition = ReportDefinition::fromArray($config);

$result = $executor->execute($definition, $reportId, true);
// Returns: ['data' => [...], 'metadata' => [...]]
```

### Exporting a Report

```php
use Modules\Reports\Services\ExportService;

$exportService = new ExportService();
$export = $exportService->export($data, 'pdf', [
    'title' => 'Monthly Revenue Report'
]);

// Download the file
header('Content-Type: ' . $export['mime_type']);
header('Content-Disposition: attachment; filename="' . $export['filename'] . '"');
echo $export['content'];
```

### Dashboard Widget

```php
use Modules\Reports\Services\DashboardApiService;

$dashboard = new DashboardApiService();
$data = $dashboard->getDashboardData('tenant-123', [
    'mobile' => true
]);
```

## Configuration

### Reports.php

Configure report behavior in `app/Modules/Reports/Config/Reports.php`:

```php
public int $maxReportRows = 10000;        # Maximum rows per report
public int $resultCacheTtl = 3600;        # Cache TTL in seconds
public bool $enableCaching = true;         # Enable result caching
public string $defaultExportFormat = 'pdf'; # Default export format
```

### ReportFields.php

Define reportable fields in `app/Modules/Reports/Config/ReportFields.php`:

```php
public array $fields = [
    'finance' => [
        'invoice_amount' => [
            'label'        => 'Invoice Amount',
            'type'         => 'decimal',
            'aggregatable' => true,
            'filterable'   => true,
            'sortable'     => true,
        ],
        // ... more fields
    ],
];
```

## Multi-Tenant Support

All report operations are tenant-scoped:

```php
$reportModel->setTenantId($tenantId);
$reports = $reportModel->findAll(); // Only returns reports for this tenant
```

## Mobile Optimization

Dashboard API responses are optimized for mobile:

```php
$data = $dashboard->getDashboardData($tenantId, ['mobile' => true]);
// Returns compressed, limited data for mobile devices
```

## Testing

Run tests for the Reports module:

```bash
vendor/bin/phpunit --filter Reports
```

## Security Considerations

- All queries are tenant-scoped to prevent data leaks
- Report configurations are validated before execution
- Export operations require proper authentication
- SQL injection protection via query builder
- Audit logging for report access (when enabled)

## Performance

- Result caching reduces database load
- Configurable row limits prevent memory issues
- Mobile responses are optimized for bandwidth
- Expired cache cleanup via scheduled tasks

## Future Enhancements

- Advanced chart visualizations
- Real-time report updates via WebSockets
- Collaborative report sharing
- AI-powered report recommendations
- Advanced data transformations
- Custom calculation fields
- Drill-down capabilities

## License

Part of the ShuleLabs CI4 project.
