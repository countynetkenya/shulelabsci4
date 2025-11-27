# 📊 Reports Development Guide

**Version**: 1.0.0
**Last Updated**: 2025-11-27
**Status**: Active

---

## 1. Overview

The Reports Module provides two types of reports:

| Type | Purpose | Location | Interface |
|:-----|:--------|:---------|:----------|
| **Standalone** | Full reports from dashboard | `Reports/Standalone/` | `ReportDefinitionInterface` |
| **Embedded** | Context tabs in entity views | `Reports/Embedded/` | `EmbeddedReportInterface` |

This guide walks you through creating both types of reports and using the core services.

---

## 2. Quick Start: Adding an Embedded Report

Embedded reports appear as tabs in entity views (Student, Parent, Staff, etc.).

### Step 1: Create the Report Class

```php
<?php

declare(strict_types=1);

namespace App\Modules\Reports\Reports\Embedded\Student;

use App\Modules\Reports\Contracts\EmbeddedReportInterface;
use App\Modules\Reports\Services\TenantService;
use App\Modules\Finance\Models\InvoiceModel;
use App\Modules\Finance\Models\PaymentModel;

class StudentFinanceTab implements EmbeddedReportInterface
{
    private InvoiceModel $invoiceModel;
    private PaymentModel $paymentModel;
    private TenantService $tenantService;

    public function __construct()
    {
        $this->invoiceModel = new InvoiceModel();
        $this->paymentModel = new PaymentModel();
        // Use TenantService to get validated school_id (never use session directly)
        $this->tenantService = service('tenant');
    }

    public function getKey(): string
    {
        return 'student_finance';
    }

    public function getTabName(): string
    {
        return lang('Reports.tabs.finance');
    }

    public function getTabIcon(): string
    {
        return 'fas fa-dollar-sign';
    }

    public function getTabOrder(): int
    {
        return 2; // After Overview (1)
    }

    public function isVisibleFor(string $entityType, int $entityId, int $viewerUserId): bool
    {
        // Check if viewer has permission to see this student's finance data
        $authService = service('authorization');

        return $authService->can('finance.view')
            || $authService->isParentOf($viewerUserId, $entityId)
            || $viewerUserId === $entityId;
    }

    public function getSummaryWidget(int $entityId): array
    {
        $schoolId = $this->tenantService->getValidatedSchoolId();
        $balance = $this->invoiceModel
            ->where('student_id', $entityId)
            ->where('school_id', $schoolId)
            ->selectSum('balance')
            ->first();

        return [
            'title' => 'Outstanding Balance',
            'value' => number_format($balance['balance'] ?? 0, 2),
            'icon' => 'fas fa-money-bill-wave',
            'trend' => $this->calculateTrend($entityId),
        ];
    }

    public function getTabContent(int $entityId, array $params = []): array
    {
        $page = $params['page'] ?? 1;
        $perPage = $params['per_page'] ?? 10;
        $schoolId = $this->tenantService->getValidatedSchoolId();

        // Get invoices with pagination
        $invoices = $this->invoiceModel
            ->where('student_id', $entityId)
            ->where('school_id', $schoolId)
            ->orderBy('created_at', 'DESC')
            ->paginate($perPage, 'default', $page);

        // Get payment history
        $payments = $this->paymentModel
            ->where('student_id', $entityId)
            ->where('school_id', $schoolId)
            ->orderBy('transaction_date', 'DESC')
            ->limit(10)
            ->findAll();

        // Calculate totals
        $totals = $this->calculateTotals($entityId);

        return [
            'summary' => $totals,
            'invoices' => [
                'data' => $invoices,
                'pager' => $this->invoiceModel->pager,
            ],
            'recent_payments' => $payments,
            'chart_data' => $this->getPaymentTrendChart($entityId),
        ];
    }

    private function calculateTotals(int $studentId): array
    {
        $schoolId = $this->tenantService->getValidatedSchoolId();

        return [
            'total_invoiced' => $this->invoiceModel
                ->where('student_id', $studentId)
                ->where('school_id', $schoolId)
                ->selectSum('amount')
                ->first()['amount'] ?? 0,
            'total_paid' => $this->paymentModel
                ->where('student_id', $studentId)
                ->where('school_id', $schoolId)
                ->selectSum('amount')
                ->first()['amount'] ?? 0,
            'outstanding' => $this->invoiceModel
                ->where('student_id', $studentId)
                ->where('school_id', $schoolId)
                ->selectSum('balance')
                ->first()['balance'] ?? 0,
        ];
    }

    private function calculateTrend(int $studentId): string
    {
        // Compare this month vs last month payments
        // Returns 'up', 'down', or 'stable'
        return 'stable';
    }

    private function getPaymentTrendChart(int $studentId): array
    {
        // Return chart-ready data for last 6 months
        return [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'data' => [0, 0, 0, 0, 0, 0],
        ];
    }
}
```

### Step 2: Register in Report Registry

Edit `app/Modules/Reports/Config/ReportRegistry.php`:

```php
return [
    'entity_tabs' => [
        'student' => [
            // ... existing tabs
            \App\Modules\Reports\Reports\Embedded\Student\StudentFinanceTab::class,
        ],
    ],
];
```

### Step 3: Add Cache Invalidation

In your module's event handlers, fire events that invalidate the report cache:

```php
// In Finance module - PaymentService.php
public function createPayment(array $data): int
{
    $paymentId = $this->paymentModel->insert($data);

    // Fire event to invalidate cached reports
    Events::trigger('payment.created', [
        'student_id' => $data['student_id'],
        'school_id' => $data['school_id'],
    ]);

    return $paymentId;
}
```

### Step 4: Write Tests

```php
<?php

namespace Tests\Feature\Reports;

use Tests\Support\TestCase;

class StudentFinanceTabTest extends TestCase
{
    public function testTabReturnsCorrectDataForStudent(): void
    {
        // Arrange
        $student = $this->createStudent();
        $this->createInvoice($student->id, 1000);
        $this->createPayment($student->id, 500);

        // Act
        $response = $this->get("/api/v1/entities/student/{$student->id}/tabs/finance");

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('data.summary.total_invoiced', 1000);
        $response->assertJsonPath('data.summary.total_paid', 500);
        $response->assertJsonPath('data.summary.outstanding', 500);
    }

    public function testTabNotVisibleToUnauthorizedUser(): void
    {
        $student = $this->createStudent();
        $otherUser = $this->createUser(); // Not parent, not admin

        $this->actingAs($otherUser);
        $response = $this->get("/api/v1/entities/student/{$student->id}/tabs/finance");

        $response->assertStatus(403);
    }
}
```

---

## 3. Quick Start: Adding a Standalone Report

Standalone reports are accessible from the Reports dashboard.

### Step 1: Create the Report Class

```php
<?php

declare(strict_types=1);

namespace App\Modules\Reports\Reports\Standalone\Finance;

use App\Modules\Reports\Contracts\ReportDefinitionInterface;
use CodeIgniter\Database\BaseBuilder;

class AgedReceivablesReport implements ReportDefinitionInterface
{
    private $db;
    private TenantService $tenantService;

    public function __construct()
    {
        $this->db = db_connect();
        $this->tenantService = service('tenant');
    }

    public function getKey(): string
    {
        return 'aged_receivables';
    }

    public function getName(): string
    {
        return lang('Reports.aged_receivables.title');
    }

    public function getModule(): string
    {
        return 'finance';
    }

    public function getSupportedFilters(): array
    {
        return [
            'date_range' => 'date_range',
            'class_id' => 'select',
            'aging_bucket' => 'multi_select', // Current, 30+, 60+, 90+
            'min_balance' => 'number',
        ];
    }

    public function getSupportedColumns(): array
    {
        return [
            'student_name' => ['label' => 'Student', 'default' => true, 'sortable' => true],
            'admission_no' => ['label' => 'Adm No', 'default' => true, 'sortable' => true],
            'class_name' => ['label' => 'Class', 'default' => true, 'sortable' => true],
            'total_invoiced' => ['label' => 'Invoiced', 'default' => true, 'sortable' => true],
            'total_paid' => ['label' => 'Paid', 'default' => true, 'sortable' => true],
            'balance' => ['label' => 'Balance', 'default' => true, 'sortable' => true],
            'days_overdue' => ['label' => 'Days Overdue', 'default' => true, 'sortable' => true],
            'last_payment_date' => ['label' => 'Last Payment', 'default' => false, 'sortable' => true],
            'parent_phone' => ['label' => 'Parent Phone', 'default' => false, 'sortable' => false],
        ];
    }

    public function supportsComparison(): bool
    {
        return true; // Allow comparing this term vs last term
    }

    public function buildQuery(array $filters): BaseBuilder
    {
        $schoolId = $this->tenantService->getValidatedSchoolId();

        $builder = $this->db->table('finance_invoices i')
            ->select([
                'u.id AS student_id',
                'CONCAT(u.first_name, " ", u.last_name) AS student_name',
                'u.admission_number AS admission_no',
                'c.name AS class_name',
                'SUM(i.amount) AS total_invoiced',
                'COALESCE(SUM(p.amount), 0) AS total_paid',
                'SUM(i.balance) AS balance',
                'DATEDIFF(NOW(), MIN(i.due_date)) AS days_overdue',
                'MAX(p.transaction_date) AS last_payment_date',
                'parent.phone AS parent_phone',
            ])
            ->join('users u', 'u.id = i.student_id')
            ->join('classes c', 'c.id = u.class_id', 'left')
            ->join('finance_payments p', 'p.student_id = i.student_id', 'left')
            ->join('user_relationships ur', 'ur.child_id = u.id AND ur.relationship = "parent"', 'left')
            ->join('users parent', 'parent.id = ur.parent_id', 'left')
            ->where('i.school_id', $schoolId)
            ->where('i.balance >', 0)
            ->groupBy('u.id');

        // Apply filters
        if (!empty($filters['class_id'])) {
            $builder->where('u.class_id', $filters['class_id']);
        }

        if (!empty($filters['min_balance'])) {
            $builder->having('balance >=', $filters['min_balance']);
        }

        if (!empty($filters['date_range'])) {
            $builder->where('i.created_at >=', $filters['date_range']['start']);
            $builder->where('i.created_at <=', $filters['date_range']['end']);
        }

        if (!empty($filters['aging_bucket'])) {
            $this->applyAgingFilter($builder, $filters['aging_bucket']);
        }

        return $builder;
    }

    public function mapRow(array $row): array
    {
        return [
            'student_id' => (int) $row['student_id'],
            'student_name' => $row['student_name'],
            'admission_no' => $row['admission_no'],
            'class_name' => $row['class_name'] ?? 'Unassigned',
            'total_invoiced' => (float) $row['total_invoiced'],
            'total_paid' => (float) $row['total_paid'],
            'balance' => (float) $row['balance'],
            'days_overdue' => max(0, (int) $row['days_overdue']),
            'last_payment_date' => $row['last_payment_date'],
            'parent_phone' => $row['parent_phone'],
            'aging_bucket' => $this->getAgingBucket((int) $row['days_overdue']),
        ];
    }

    public function getDrillDownRoute(array $row): ?string
    {
        if (!service('authorization')->can('students.view')) {
            return null;
        }

        return route_to('students.show', $row['student_id']);
    }

    public function getPrerequisites(): array
    {
        return ['finance.reports.view'];
    }

    private function applyAgingFilter(BaseBuilder $builder, array $buckets): void
    {
        $conditions = [];

        foreach ($buckets as $bucket) {
            switch ($bucket) {
                case 'current':
                    $conditions[] = 'DATEDIFF(NOW(), i.due_date) <= 0';
                    break;
                case '30+':
                    $conditions[] = 'DATEDIFF(NOW(), i.due_date) BETWEEN 1 AND 30';
                    break;
                case '60+':
                    $conditions[] = 'DATEDIFF(NOW(), i.due_date) BETWEEN 31 AND 60';
                    break;
                case '90+':
                    $conditions[] = 'DATEDIFF(NOW(), i.due_date) > 60';
                    break;
            }
        }

        if (!empty($conditions)) {
            $builder->groupStart();
            foreach ($conditions as $i => $condition) {
                if ($i === 0) {
                    $builder->where($condition);
                } else {
                    $builder->orWhere($condition);
                }
            }
            $builder->groupEnd();
        }
    }

    private function getAgingBucket(int $daysOverdue): string
    {
        if ($daysOverdue <= 0) {
            return 'current';
        }
        if ($daysOverdue <= 30) {
            return '1-30 days';
        }
        if ($daysOverdue <= 60) {
            return '31-60 days';
        }

        return '60+ days';
    }
}
```

### Step 2: Register in Report Registry

Edit `app/Modules/Reports/Config/ReportRegistry.php`:

```php
return [
    'standalone_reports' => [
        // ... existing reports
        \App\Modules\Reports\Reports\Standalone\Finance\AgedReceivablesReport::class,
    ],
];
```

### Step 3: Add API Route (if not using auto-discovery)

```php
// In Reports/Config/Routes.php
$routes->group('api/v1/reports', ['namespace' => 'App\Modules\Reports\Controllers\Api'], static function ($routes) {
    $routes->get('/', 'ReportApiController::index');
    $routes->get('(:segment)', 'ReportApiController::show/$1');
    $routes->post('(:segment)', 'ReportApiController::generate/$1');
    $routes->post('(:segment)/export', 'ReportApiController::export/$1');
});
```

### Step 4: Write Tests

```php
<?php

namespace Tests\Feature\Reports;

use Tests\Support\TestCase;

class AgedReceivablesReportTest extends TestCase
{
    public function testReportReturnsDataWithFilters(): void
    {
        // Arrange
        $this->seed('FinanceTestSeeder');
        $this->actingAsAdmin();

        // Act
        $response = $this->post('/api/v1/reports/aged_receivables', [
            'filters' => [
                'min_balance' => 100,
            ],
        ]);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['student_id', 'student_name', 'balance', 'aging_bucket'],
            ],
            'meta' => ['total', 'page', 'per_page'],
        ]);
    }

    public function testReportEnforcesTenantIsolation(): void
    {
        // Create data for two schools
        $school1 = $this->createSchool();
        $school2 = $this->createSchool();

        $this->createInvoiceForSchool($school1->id);
        $this->createInvoiceForSchool($school2->id);

        // Act as admin of school1
        $this->actingAsAdminOfSchool($school1->id);
        $response = $this->post('/api/v1/reports/aged_receivables');

        // Assert only school1 data returned
        $data = $response->json('data');
        foreach ($data as $row) {
            $this->assertEquals($school1->id, $row['school_id']);
        }
    }

    public function testExportCreatesAsyncJobForLargeReports(): void
    {
        // Seed 5000 invoices
        $this->seedLargeDataset();
        $this->actingAsAdmin();

        $response = $this->post('/api/v1/reports/aged_receivables/export', [
            'format' => 'excel',
        ]);

        $response->assertStatus(202); // Accepted
        $response->assertJsonPath('status', 'queued');
        $response->assertJsonStructure(['job_id', 'message']);
    }
}
```

---

## 4. Using Core Services

### 4.1 DateRangeFactory

Provides standardized date range presets:

```php
use App\Modules\Reports\Services\DateRangeFactory;

$factory = new DateRangeFactory();

// Get predefined ranges
$thisWeek = $factory->thisWeek();    // ['start' => Carbon, 'end' => Carbon]
$thisMonth = $factory->thisMonth();
$thisTerm = $factory->thisTerm();    // Uses academic calendar
$lastYear = $factory->lastYear();

// Parse user input
$custom = $factory->parse('2025-01-01', '2025-03-31');

// Validate range
if (!$factory->isValidRange($start, $end)) {
    throw new ValidationException('Invalid date range');
}
```

### 4.2 FilterEngine

Applies dynamic filters to query builders:

```php
use App\Modules\Reports\Services\FilterEngine;

$engine = new FilterEngine();

// Define available filters
$filterDefs = [
    'class_id' => ['type' => 'select', 'options' => $classes],
    'status' => ['type' => 'multi_select', 'options' => ['paid', 'partial', 'unpaid']],
    'date_range' => ['type' => 'date_range'],
    'amount' => ['type' => 'range', 'min' => 0, 'max' => 100000],
];

// Apply filters to builder
$builder = $engine->apply($builder, $filterDefs, $userFilters);
```

### 4.3 ComparisonEngine

Enables period-over-period comparison:

```php
use App\Modules\Reports\Services\ComparisonEngine;

$engine = new ComparisonEngine();

// Compare this month vs last month
$comparison = $engine->compare(
    currentData: $thisMonthResults,
    previousData: $lastMonthResults,
    groupBy: 'class_id',
    metrics: ['total_collected', 'student_count']
);

// Result includes variance and percentage change
// [
//     'class_id' => 1,
//     'total_collected' => ['current' => 5000, 'previous' => 4000, 'change' => 1000, 'percent' => 25.0],
//     'student_count' => ['current' => 30, 'previous' => 28, 'change' => 2, 'percent' => 7.14],
// ]
```

### 4.4 ExportService

Handles report exports:

```php
use App\Modules\Reports\Services\ExportService;

$exporter = new ExportService();

// Export to PDF
$pdfPath = $exporter->toPdf($reportData, [
    'title' => 'Aged Receivables',
    'orientation' => 'landscape',
    'template' => 'school_letterhead', // Uses report_templates
]);

// Export to Excel
$excelPath = $exporter->toExcel($reportData, [
    'title' => 'Aged Receivables',
    'sheets' => ['Summary', 'Details'],
]);

// For large exports, use async
$jobId = $exporter->queueExport($reportKey, $filters, $format, $userId);
```

### 4.5 CacheService

Manages report caching:

```php
use App\Modules\Reports\Services\CacheService;

$cache = new CacheService();

// Generate cache key
$key = $cache->generateKey('aged_receivables', $filters, $schoolId);

// Check cache
if ($cached = $cache->get($key)) {
    return $cached;
}

// Store with TTL
$cache->put($key, $data, ttl: 300); // 5 minutes

// Invalidate by pattern
$cache->invalidate('aged_receivables'); // All variations
$cache->invalidate('student_finance_123'); // Specific student
```

---

## 5. Best Practices

### 5.1 Always Apply Tenant Scope

**Every query must include school_id filtering**. Use the `TenantService` to get a validated school_id that verifies the user has access:

```php
// ❌ BAD - Direct session access without validation
$builder->from('finance_invoices')
    ->where('school_id', session('school_id'));

// ✅ GOOD - Use TenantService for validated school_id
$schoolId = $this->tenantService->getValidatedSchoolId();
$builder->from('finance_invoices')
    ->where('school_id', $schoolId);
```

**Why use TenantService?**
- Validates the session school_id belongs to the authenticated user
- Prevents session manipulation attacks
- Centralizes tenant logic for consistency
- Throws exception if school_id is invalid

### 5.2 Use Aggregate Tables for Large Data

For reports on high-volume data (payments, attendance), use pre-computed aggregates:

```php
// ❌ BAD - Slow on large datasets
$builder->from('finance_payments')
    ->select('DATE(transaction_date) as date, SUM(amount) as total')
    ->groupBy('date');

// ✅ GOOD - Use pre-computed aggregate
$builder->from('daily_fee_summaries')
    ->select('summary_date, total_collected')
    ->where('summary_date BETWEEN', [$start, $end]);
```

### 5.3 Implement Proper Caching

Use appropriate TTLs based on data volatility:

```php
// High-volatility (payments) - short TTL
$cache->put($key, $data, ttl: 60); // 1 minute

// Low-volatility (enrollment) - longer TTL
$cache->put($key, $data, ttl: 3600); // 1 hour

// Real-time required - no cache
// Direct query without caching
```

### 5.4 Handle Large Exports Asynchronously

Reports exceeding thresholds must queue:

```php
public function export(string $format): ResponseInterface
{
    $rowCount = $this->getEstimatedRowCount();
    // Use configurable threshold from app config
    $asyncThreshold = config('Reports')->asyncThreshold ?? 1000;

    if ($rowCount > $asyncThreshold) {
        $jobId = $this->queueService->dispatch(
            new GenerateReportJob($this->reportKey, $this->filters, auth()->id(), $format)
        );

        return $this->response
            ->setStatusCode(202)
            ->setJSON([
                'status' => 'queued',
                'job_id' => $jobId,
                'message' => 'Export is being generated. You will be notified when ready.',
            ]);
    }

    return $this->exportSync($format);
}
```

### 5.5 Validate Drill-Down Permissions

Always check permissions before providing drill-down links:

```php
public function getDrillDownRoute(array $row): ?string
{
    // Check if user can view the target entity
    if (!service('authorization')->can('students.view')) {
        return null;
    }

    // Check if user can view THIS specific student (tenant isolation)
    $student = $this->studentModel->find($row['student_id']);
    $schoolId = $this->tenantService->getValidatedSchoolId();
    if ($student['school_id'] !== $schoolId) {
        return null;
    }

    return route_to('students.show', $row['student_id']);
}
```

---

## 6. Testing Reports

### 6.1 Unit Tests

Test individual components:

```php
// Test DateRangeFactory
public function testThisTermReturnsCorrectDates(): void
{
    $factory = new DateRangeFactory();
    $range = $factory->thisTerm();

    $this->assertEquals('2025-01-06', $range['start']->format('Y-m-d'));
    $this->assertEquals('2025-04-04', $range['end']->format('Y-m-d'));
}

// Test FilterEngine
public function testFilterEngineAppliesMultiSelect(): void
{
    $engine = new FilterEngine();
    $builder = $this->db->table('invoices');

    $filtered = $engine->apply($builder, [
        'status' => ['type' => 'multi_select'],
    ], [
        'status' => ['paid', 'partial'],
    ]);

    $this->assertStringContains("status IN ('paid','partial')", $filtered->getCompiledSelect());
}
```

### 6.2 Feature Tests

Test complete report flows:

```php
public function testAgedReceivablesReportEndToEnd(): void
{
    // Seed data
    $this->seed('FinanceTestSeeder');

    // Generate report
    $response = $this->actingAsAdmin()
        ->post('/api/v1/reports/aged_receivables', [
            'filters' => ['min_balance' => 100],
            'columns' => ['student_name', 'balance', 'days_overdue'],
        ]);

    $response->assertStatus(200);
    $response->assertJsonCount(3, 'data.0'); // Only requested columns
}
```

### 6.3 Performance Tests

Ensure reports perform well at scale:

```php
public function testReportPerformanceWithLargeDataset(): void
{
    // Seed 10,000 records
    $this->seedLargeDataset(10000);

    $start = microtime(true);
    $response = $this->post('/api/v1/reports/aged_receivables');
    $duration = microtime(true) - $start;

    $this->assertLessThan(2.0, $duration, 'Report took too long');
    $response->assertStatus(200);
}
```

---

## 7. Common Patterns

### 7.1 Cross-Module Report

Reports that span multiple modules:

```php
class Student360Report implements ReportDefinitionInterface
{
    private FinanceService $financeService;
    private AcademicService $academicService;
    private LibraryService $libraryService;
    private TenantService $tenantService;

    public function __construct(
        FinanceService $financeService,
        AcademicService $academicService,
        LibraryService $libraryService
    ) {
        $this->financeService = $financeService;
        $this->academicService = $academicService;
        $this->libraryService = $libraryService;
        $this->tenantService = service('tenant');
    }

    public function buildQuery(array $filters): BaseBuilder
    {
        $schoolId = $this->tenantService->getValidatedSchoolId();

        // Start with students table
        $builder = $this->db->table('users u')
            ->select('u.id, u.first_name, u.last_name')
            ->where('u.role', 'student')
            ->where('u.school_id', $schoolId);

        // Join aggregated finance data
        $builder->join('(SELECT student_id, SUM(balance) as outstanding FROM finance_invoices GROUP BY student_id) fin',
            'fin.student_id = u.id', 'left');

        // Join academic summary
        $builder->join('(SELECT student_id, AVG(score) as avg_grade FROM grades GROUP BY student_id) acad',
            'acad.student_id = u.id', 'left');

        // Join library activity
        $builder->join('(SELECT borrower_id, COUNT(*) as books_borrowed FROM library_transactions WHERE returned_at IS NULL GROUP BY borrower_id) lib',
            'lib.borrower_id = u.id', 'left');

        return $builder;
    }
}
```

### 7.2 Scheduled Report with Email Delivery

```php
// In ProcessScheduledReportsJob
public function handle(): void
{
    $schedules = $this->scheduleModel
        ->where('is_active', true)
        ->where('next_run_at <=', date('Y-m-d H:i:s'))
        ->findAll();

    foreach ($schedules as $schedule) {
        try {
            // Generate report
            $report = $this->reportBuilder->generate(
                $schedule['report_key'],
                json_decode($schedule['filters'], true)
            );

            // Create snapshot
            $snapshot = $this->snapshotService->create($report, $schedule);

            // Email to recipients
            $recipients = json_decode($schedule['recipients'], true);
            $this->emailService->sendReportEmail($recipients, $snapshot);

            // Update schedule
            $this->scheduleModel->update($schedule['id'], [
                'last_run_at' => date('Y-m-d H:i:s'),
                'next_run_at' => $this->calculateNextRun($schedule),
            ]);
        } catch (\Exception $e) {
            log_message('error', "Scheduled report failed: {$e->getMessage()}");
        }
    }
}
```

---

## 8. Troubleshooting

### Report Returns Empty Data

1. **Check tenant scope**: Verify `school_id` filter is applied.
2. **Check date range**: Ensure filters include data within range.
3. **Check permissions**: Verify user has required permissions.

### Slow Report Generation

1. **Check indexes**: Ensure filtered/joined columns are indexed.
2. **Use aggregates**: For large datasets, use pre-computed aggregates.
3. **Add caching**: Cache results with appropriate TTL.

### Export Fails

1. **Check memory**: Large exports may exceed PHP memory limit.
2. **Use async**: Queue large exports instead of sync generation.
3. **Check disk space**: Ensure writable directory has space.

---

## 9. Related Documentation

- [Reports Module Specification](../specs/08-REPORTS_SPEC.md)
- [Reports Integration Checklist](REPORTS_INTEGRATION_CHECKLIST.md)
- [Module Development Guide](MODULES.md)
- [Testing Guide](TESTING.md)
