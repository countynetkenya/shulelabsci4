# 📊 Reports Module Specification

**Version**: 1.0.0
**Status**: Draft
**Last Updated**: 2025-11-27

---

## Part 1: Feature Definition (The "What" & "Why")

### 1.1 Overview

The Reports Module is the **"Intelligence Layer"** of the ShuleLabs ecosystem. It aggregates and presents data from all other modules (Finance, Academic, Library, Transport, Hostel, Inventory, HR) to provide actionable insights for school administrators, teachers, parents, and students.

The module provides two primary report delivery mechanisms:

1. **Standalone Reports** - Full-featured reports accessible from a central Reports dashboard (e.g., Aged Receivables, Term Summary, Enrollment Trends).

2. **Embedded Reports** - Contextual reports displayed as tabs within entity views (e.g., Student Finance Tab, Parent Fee Statement, Staff Payroll History).

### 1.2 User Stories

#### Standalone Reports

- **As a Bursar**, I want to generate an Aged Receivables report filtered by class and date range, so that I can follow up on overdue payments.
- **As a Principal**, I want to view a Term Summary dashboard showing enrollment, finances, and academic performance, so that I can present to the board.
- **As an Admin**, I want to schedule a Weekly Fee Collection report to be emailed every Monday, so that I don't have to generate it manually.
- **As a Librarian**, I want to view a Book Circulation report showing most borrowed books and overdue items, so that I can manage inventory effectively.

#### Embedded Reports

- **As a Parent**, I want to view my child's unified fee statement in the Student view, so that I can see all financial obligations in one place.
- **As a Teacher**, I want to see a Student 360° profile combining academic grades, attendance, and library activity, so that I can provide holistic support.
- **As a Finance Officer**, I want to see a Class Finance Tab showing fee collection rates per class, so that I can identify underperforming classes.
- **As a Warden**, I want to see Room occupancy history and fee status in the Room view, so that I can manage allocations.

#### Cross-Module Reports

- **As an Admin**, I want a Student 360° Report combining Finance (balances), Academic (grades), Library (borrowed books), Transport (route), and Hostel (room allocation), so that I have complete visibility.
- **As a Principal**, I want a School Dashboard showing aggregated metrics across all modules, so that I can monitor overall school health.

### 1.3 User Workflows

#### 1.3.1 Embedded Report View (Student Finance Tab)

1. User navigates to Student profile view.
2. System loads student overview with tabs (Overview, Finance, Academic, Library, etc.).
3. User clicks "Finance" tab.
4. System fetches embedded report data via API: `GET /api/v1/entities/student/{id}/tabs/finance`.
5. Tab displays fee summary widget, invoice list, and payment history.
6. User can click "View Full Report" to open standalone Aged Receivables filtered to this student.

#### 1.3.2 Standalone Report Generation

1. User navigates to Reports dashboard.
2. User selects "Aged Receivables" from report catalog.
3. System displays filter panel (Date Range, Class, Status).
4. User applies filters and clicks "Generate".
5. System displays report with drill-down capability.
6. User clicks on a row to see student detail.
7. User exports report to PDF or Excel.

#### 1.3.3 Scheduled Report Delivery

1. Admin navigates to Reports > Schedules.
2. Admin clicks "Create Schedule".
3. Admin selects report, filters, frequency (Daily/Weekly/Monthly), and recipients.
4. System saves schedule and displays next run time.
5. Cron job runs `ProcessScheduledReportsJob` at midnight.
6. System generates report, saves snapshot, and emails recipients.

### 1.4 Acceptance Criteria

- [ ] Standalone reports support filtering, sorting, and pagination.
- [ ] Embedded reports display correctly in entity view tabs.
- [ ] Reports support export to PDF and Excel formats.
- [ ] Scheduled reports generate and deliver via email.
- [ ] All reports enforce tenant isolation (school_id scoping).
- [ ] Large reports (>1000 rows) process asynchronously with progress indication.
- [ ] Report snapshots are immutable and timestamped for audit purposes.
- [ ] Cache invalidation correctly refreshes report data when source data changes.

---

## Part 2: Technical Specification (The "How")

### 2.1 Module Structure

```
app/Modules/Reports/
├── Config/
│   ├── Routes.php              # API and Web routes
│   ├── Services.php            # Service registration
│   └── ReportRegistry.php      # Report catalog registration
├── Contracts/
│   ├── ReportDefinitionInterface.php   # Standalone report contract
│   ├── EmbeddedReportInterface.php     # Embedded report contract
│   ├── FilterableInterface.php         # Filter capability
│   └── ExportableInterface.php         # Export capability
├── Controllers/
│   ├── Api/
│   │   ├── ReportApiController.php     # Standalone report API
│   │   ├── EntityViewController.php    # Embedded report API
│   │   └── ScheduleApiController.php   # Schedule management API
│   └── Web/
│       ├── ReportWebController.php     # Web dashboard
│       └── ScheduleWebController.php   # Schedule management UI
├── Services/
│   ├── ReportBuilder.php        # Core report generation
│   ├── DateRangeFactory.php     # Date range presets
│   ├── FilterEngine.php         # Dynamic filter application
│   ├── ComparisonEngine.php     # Period-over-period comparison
│   ├── ColumnManager.php        # Dynamic column selection
│   ├── GroupingEngine.php       # Group-by functionality
│   ├── ExportService.php        # Export orchestration
│   ├── CacheService.php         # Report caching
│   ├── SchedulerService.php     # Schedule management
│   ├── SnapshotService.php      # Immutable snapshots
│   ├── AggregateService.php     # Aggregate table management
│   └── DrillDownService.php     # Drill-down navigation
├── Libraries/
│   ├── PdfExporter.php          # PDF generation
│   ├── ExcelExporter.php        # Excel generation
│   └── ChartRenderer.php        # Chart/graph rendering
├── Models/
│   ├── ScheduledReportModel.php
│   ├── ReportSnapshotModel.php
│   ├── ReportAccessLogModel.php
│   ├── ReportTemplateModel.php
│   └── Aggregates/
│       └── DailyFeeSummaryModel.php
├── Jobs/
│   ├── GenerateReportJob.php           # Async report generation
│   └── ProcessScheduledReportsJob.php  # Cron job for schedules
├── Filters/
│   └── ReportRateLimiter.php    # Rate limiting for exports
├── Reports/
│   ├── Standalone/
│   │   ├── AgedReceivablesReport.php
│   │   ├── TermSummaryReport.php
│   │   ├── EnrollmentTrendsReport.php
│   │   └── BookCirculationReport.php
│   └── Embedded/
│       ├── Student/
│       │   ├── StudentFinanceTab.php
│       │   ├── StudentAcademicTab.php
│       │   ├── StudentLibraryTab.php
│       │   └── StudentTransportTab.php
│       ├── Parent/
│       │   ├── ParentFinanceTab.php
│       │   └── ParentChildrenTab.php
│       └── Staff/
│           ├── StaffPayrollTab.php
│           └── StaffClassesTab.php
├── Views/
│   ├── dashboard/
│   │   ├── index.php            # Report catalog
│   │   └── report.php           # Single report view
│   ├── components/
│   │   ├── filter_panel.php     # Reusable filter UI
│   │   ├── data_table.php       # Reusable table UI
│   │   └── summary_widget.php   # Reusable widget UI
│   └── exports/
│       ├── pdf_template.php     # PDF layout
│       └── excel_template.php   # Excel layout
├── Database/
│   └── Migrations/
│       ├── 2025-11-27-000001_CreateScheduledReports.php
│       ├── 2025-11-27-000002_CreateReportSnapshots.php
│       ├── 2025-11-27-000003_CreateReportAccessLogs.php
│       ├── 2025-11-27-000004_CreateReportTemplates.php
│       └── 2025-11-27-000005_CreateDailyFeeSummaries.php
├── Tests/
│   ├── Unit/
│   │   ├── DateRangeFactoryTest.php
│   │   └── FilterEngineTest.php
│   └── Feature/
│       ├── AgedReceivablesTest.php
│       └── EmbeddedReportTest.php
└── Language/
    ├── en/
    │   └── Reports.php
    └── sw/
        └── Reports.php
```

### 2.2 Database Schema

#### `scheduled_reports` (Report Automation)

```sql
CREATE TABLE scheduled_reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    report_key VARCHAR(100) NOT NULL,     -- e.g., 'aged_receivables'
    name VARCHAR(255) NOT NULL,            -- User-defined name
    filters JSON,                          -- Saved filter configuration
    frequency ENUM('daily', 'weekly', 'monthly') NOT NULL,
    day_of_week TINYINT NULL,              -- 0-6 for weekly
    day_of_month TINYINT NULL,             -- 1-31 for monthly
    recipients JSON NOT NULL,              -- Array of email addresses
    last_run_at DATETIME NULL,
    next_run_at DATETIME NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_next_run (next_run_at, is_active)
);
```

#### `report_snapshots` (Immutable Official Documents)

```sql
CREATE TABLE report_snapshots (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    report_key VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    filters JSON,
    generated_at DATETIME NOT NULL,
    generated_by INT NOT NULL,
    row_count INT NOT NULL,
    data_hash VARCHAR(64) NOT NULL,        -- SHA-256 for integrity
    storage_path VARCHAR(500) NOT NULL,    -- Path to stored file
    file_type ENUM('pdf', 'excel', 'json') NOT NULL,
    file_size INT NOT NULL,                -- Bytes
    schedule_id INT NULL,                  -- If from scheduled job
    is_official BOOLEAN DEFAULT FALSE,     -- Marked as official record
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (generated_by) REFERENCES users(id),
    FOREIGN KEY (schedule_id) REFERENCES scheduled_reports(id) ON DELETE SET NULL,
    INDEX idx_lookup (school_id, report_key, generated_at)
);
```

#### `report_access_logs` (Audit Trail)

```sql
CREATE TABLE report_access_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    user_id INT NOT NULL,
    report_key VARCHAR(100) NOT NULL,
    access_type ENUM('view', 'export', 'schedule', 'drill_down') NOT NULL,
    filters JSON,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    duration_ms INT,                       -- Query execution time
    row_count INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_activity (user_id, created_at),
    INDEX idx_report_usage (school_id, report_key, created_at)
);
```

#### `report_templates` (School Branding)

```sql
CREATE TABLE report_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    type ENUM('header', 'footer', 'full') NOT NULL,
    logo_path VARCHAR(500) NULL,
    header_html TEXT NULL,
    footer_html TEXT NULL,
    css_styles TEXT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    -- Note: Application logic should enforce only one default per (school_id, type)
    INDEX idx_school_type (school_id, type)
);
```

#### `daily_fee_summaries` (Aggregate Table Example)

```sql
CREATE TABLE daily_fee_summaries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    summary_date DATE NOT NULL,
    class_id INT NOT NULL DEFAULT 0,       -- 0 for school-wide (use 0 instead of NULL for unique constraint)
    total_invoiced DECIMAL(15, 2) DEFAULT 0.00,
    total_collected DECIMAL(15, 2) DEFAULT 0.00,
    total_outstanding DECIMAL(15, 2) DEFAULT 0.00,
    student_count INT DEFAULT 0,
    invoice_count INT DEFAULT 0,
    payment_count INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    UNIQUE KEY unique_daily (school_id, summary_date, class_id),
    INDEX idx_date_range (school_id, summary_date)
);
```

### 2.3 Core Contracts

#### `ReportDefinitionInterface` (Standalone Reports)

```php
<?php

declare(strict_types=1);

namespace App\Modules\Reports\Contracts;

use CodeIgniter\Database\BaseBuilder;

interface ReportDefinitionInterface
{
    /**
     * Unique identifier for the report.
     * Example: 'aged_receivables', 'term_summary'
     */
    public function getKey(): string;

    /**
     * Human-readable name.
     * Example: 'Aged Receivables Report'
     */
    public function getName(): string;

    /**
     * Source module for categorization.
     * Example: 'finance', 'academic', 'library'
     */
    public function getModule(): string;

    /**
     * Available filters with their types.
     * Example: ['date_range' => 'date_range', 'class_id' => 'select', 'status' => 'multi_select']
     *
     * @return array<string, string>
     */
    public function getSupportedFilters(): array;

    /**
     * Available columns with visibility options.
     * Example: ['student_name' => ['label' => 'Student', 'default' => true], ...]
     *
     * @return array<string, array{label: string, default: bool, sortable?: bool}>
     */
    public function getSupportedColumns(): array;

    /**
     * Whether this report supports period comparison.
     */
    public function supportsComparison(): bool;

    /**
     * Build the query with applied filters.
     *
     * @param array<string, mixed> $filters
     */
    public function buildQuery(array $filters): BaseBuilder;

    /**
     * Transform a database row to output format.
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    public function mapRow(array $row): array;

    /**
     * Get drill-down route for a row.
     *
     * @param array<string, mixed> $row
     */
    public function getDrillDownRoute(array $row): ?string;

    /**
     * Required permissions to view this report.
     *
     * @return array<string>
     */
    public function getPrerequisites(): array;
}
```

#### `EmbeddedReportInterface` (Entity View Tabs)

```php
<?php

declare(strict_types=1);

namespace App\Modules\Reports\Contracts;

interface EmbeddedReportInterface
{
    /**
     * Unique key for the embedded report.
     * Example: 'student_finance', 'parent_children'
     */
    public function getKey(): string;

    /**
     * Display name for the tab.
     * Example: 'Finance', 'Academic'
     */
    public function getTabName(): string;

    /**
     * Icon class for the tab.
     * Example: 'fas fa-dollar-sign', 'fas fa-graduation-cap'
     */
    public function getTabIcon(): string;

    /**
     * Sort order for tabs (lower = first).
     */
    public function getTabOrder(): int;

    /**
     * Check if tab should be visible for this entity.
     *
     * @param string $entityType  e.g., 'student', 'parent'
     * @param int    $entityId
     * @param int    $viewerUserId
     */
    public function isVisibleFor(string $entityType, int $entityId, int $viewerUserId): bool;

    /**
     * Get summary widget data for quick view.
     *
     * @param int $entityId
     * @return array{title: string, value: string|int|float, trend?: string, icon?: string}
     */
    public function getSummaryWidget(int $entityId): array;

    /**
     * Get full tab content data.
     *
     * @param int                  $entityId
     * @param array<string, mixed> $params   Pagination, filters, etc.
     * @return array<string, mixed>
     */
    public function getTabContent(int $entityId, array $params = []): array;
}
```

### 2.4 API Endpoints

#### Standalone Reports

| Method | Endpoint | Description | Access |
|:-------|:---------|:------------|:-------|
| GET | `/api/v1/reports` | List available reports | Auth |
| GET | `/api/v1/reports/{key}` | Get report metadata (filters, columns) | Auth |
| POST | `/api/v1/reports/{key}` | Generate report with filters | Auth |
| POST | `/api/v1/reports/{key}/export` | Export report (PDF/Excel) | Auth |
| POST | `/api/v1/reports/{key}/async` | Queue large report generation | Auth |
| GET | `/api/v1/reports/jobs/{id}` | Check async job status | Auth |

#### Embedded Reports (Entity Views)

| Method | Endpoint | Description | Access |
|:-------|:---------|:------------|:-------|
| GET | `/api/v1/entities/{type}/{id}/view` | Get entity view with all tabs | Auth |
| GET | `/api/v1/entities/{type}/{id}/tabs` | List available tabs for entity | Auth |
| GET | `/api/v1/entities/{type}/{id}/tabs/{tab}` | Get specific tab content | Auth |
| GET | `/api/v1/entities/{type}/{id}/widgets` | Get summary widgets for entity | Auth |

#### Scheduled Reports

| Method | Endpoint | Description | Access |
|:-------|:---------|:------------|:-------|
| GET | `/api/v1/reports/schedules` | List user's scheduled reports | Auth |
| POST | `/api/v1/reports/schedules` | Create new schedule | Admin |
| GET | `/api/v1/reports/schedules/{id}` | Get schedule details | Auth |
| PUT | `/api/v1/reports/schedules/{id}` | Update schedule | Admin |
| DELETE | `/api/v1/reports/schedules/{id}` | Delete schedule | Admin |
| POST | `/api/v1/reports/schedules/{id}/run` | Trigger immediate run | Admin |

#### Report Snapshots

| Method | Endpoint | Description | Access |
|:-------|:---------|:------------|:-------|
| GET | `/api/v1/reports/snapshots` | List saved snapshots | Auth |
| GET | `/api/v1/reports/snapshots/{id}` | Download snapshot file | Auth |
| POST | `/api/v1/reports/snapshots/{id}/official` | Mark as official | Admin |

### 2.5 Embedded Report Registry

The registry maps entity types to their available embedded report tabs:

```php
<?php

declare(strict_types=1);

// Config/ReportRegistry.php

return [
    'entity_tabs' => [
        'student' => [
            \App\Modules\Reports\Reports\Embedded\Student\StudentOverviewTab::class,
            \App\Modules\Reports\Reports\Embedded\Student\StudentFinanceTab::class,
            \App\Modules\Reports\Reports\Embedded\Student\StudentAcademicTab::class,
            \App\Modules\Reports\Reports\Embedded\Student\StudentLibraryTab::class,
            \App\Modules\Reports\Reports\Embedded\Student\StudentTransportTab::class,
            \App\Modules\Reports\Reports\Embedded\Student\StudentInventoryTab::class,
            \App\Modules\Reports\Reports\Embedded\Student\StudentHostelTab::class,
            \App\Modules\Reports\Reports\Embedded\Student\StudentThreadsTab::class,
        ],
        'parent' => [
            \App\Modules\Reports\Reports\Embedded\Parent\ParentOverviewTab::class,
            \App\Modules\Reports\Reports\Embedded\Parent\ParentChildrenTab::class,
            \App\Modules\Reports\Reports\Embedded\Parent\ParentFinanceTab::class,
            \App\Modules\Reports\Reports\Embedded\Parent\ParentAcademicTab::class,
            \App\Modules\Reports\Reports\Embedded\Parent\ParentThreadsTab::class,
        ],
        'staff' => [
            \App\Modules\Reports\Reports\Embedded\Staff\StaffOverviewTab::class,
            \App\Modules\Reports\Reports\Embedded\Staff\StaffHRTab::class,
            \App\Modules\Reports\Reports\Embedded\Staff\StaffPayrollTab::class,
            \App\Modules\Reports\Reports\Embedded\Staff\StaffClassesTab::class,
            \App\Modules\Reports\Reports\Embedded\Staff\StaffThreadsTab::class,
        ],
        'class' => [
            \App\Modules\Reports\Reports\Embedded\ClassRoom\ClassOverviewTab::class,
            \App\Modules\Reports\Reports\Embedded\ClassRoom\ClassStudentsTab::class,
            \App\Modules\Reports\Reports\Embedded\ClassRoom\ClassFinanceTab::class,
            \App\Modules\Reports\Reports\Embedded\ClassRoom\ClassAcademicTab::class,
            \App\Modules\Reports\Reports\Embedded\ClassRoom\ClassAttendanceTab::class,
            \App\Modules\Reports\Reports\Embedded\ClassRoom\ClassThreadsTab::class,
        ],
        'book' => [
            \App\Modules\Reports\Reports\Embedded\Library\BookDetailsTab::class,
            \App\Modules\Reports\Reports\Embedded\Library\BookAvailabilityTab::class,
            \App\Modules\Reports\Reports\Embedded\Library\BookHistoryTab::class,
        ],
        'inventory_item' => [
            \App\Modules\Reports\Reports\Embedded\Inventory\ItemDetailsTab::class,
            \App\Modules\Reports\Reports\Embedded\Inventory\ItemStockTab::class,
            \App\Modules\Reports\Reports\Embedded\Inventory\ItemTransactionsTab::class,
            \App\Modules\Reports\Reports\Embedded\Inventory\ItemIssuedToTab::class,
        ],
        'room' => [
            \App\Modules\Reports\Reports\Embedded\Hostel\RoomDetailsTab::class,
            \App\Modules\Reports\Reports\Embedded\Hostel\RoomOccupantsTab::class,
            \App\Modules\Reports\Reports\Embedded\Hostel\RoomHistoryTab::class,
            \App\Modules\Reports\Reports\Embedded\Hostel\RoomFinanceTab::class,
        ],
        'route' => [
            \App\Modules\Reports\Reports\Embedded\Transport\RouteOverviewTab::class,
            \App\Modules\Reports\Reports\Embedded\Transport\RouteStopsTab::class,
            \App\Modules\Reports\Reports\Embedded\Transport\RouteStudentsTab::class,
            \App\Modules\Reports\Reports\Embedded\Transport\RouteScheduleTab::class,
            \App\Modules\Reports\Reports\Embedded\Transport\RouteFinanceTab::class,
        ],
        'school' => [
            \App\Modules\Reports\Reports\Embedded\School\SchoolOverviewTab::class,
            \App\Modules\Reports\Reports\Embedded\School\SchoolEnrollmentTab::class,
            \App\Modules\Reports\Reports\Embedded\School\SchoolFinanceTab::class,
            \App\Modules\Reports\Reports\Embedded\School\SchoolStaffTab::class,
            \App\Modules\Reports\Reports\Embedded\School\SchoolPerformanceTab::class,
        ],
    ],

    'standalone_reports' => [
        // Finance Reports
        \App\Modules\Reports\Reports\Standalone\Finance\AgedReceivablesReport::class,
        \App\Modules\Reports\Reports\Standalone\Finance\FeeCollectionReport::class,
        \App\Modules\Reports\Reports\Standalone\Finance\PaymentHistoryReport::class,
        \App\Modules\Reports\Reports\Standalone\Finance\OutstandingBalancesReport::class,

        // Academic Reports
        \App\Modules\Reports\Reports\Standalone\Academic\TermSummaryReport::class,
        \App\Modules\Reports\Reports\Standalone\Academic\ClassPerformanceReport::class,
        \App\Modules\Reports\Reports\Standalone\Academic\AttendanceReport::class,

        // Library Reports
        \App\Modules\Reports\Reports\Standalone\Library\BookCirculationReport::class,
        \App\Modules\Reports\Reports\Standalone\Library\OverdueBooksReport::class,

        // HR Reports
        \App\Modules\Reports\Reports\Standalone\HR\StaffDirectoryReport::class,
        \App\Modules\Reports\Reports\Standalone\HR\PayrollSummaryReport::class,

        // Cross-Module Reports
        \App\Modules\Reports\Reports\Standalone\CrossModule\Student360Report::class,
        \App\Modules\Reports\Reports\Standalone\CrossModule\EnrollmentTrendsReport::class,
        \App\Modules\Reports\Reports\Standalone\CrossModule\SchoolDashboardReport::class,
    ],
];
```

### 2.6 Cache Invalidation Events

Reports must invalidate their cache when underlying data changes:

```php
<?php

// Config/Events.php - Cache Invalidation Mapping

return [
    'cache_invalidation' => [
        // Finance Events
        'payment.created' => ['aged_receivables', 'fee_collection', 'student_finance_*'],
        'payment.updated' => ['aged_receivables', 'fee_collection', 'student_finance_*'],
        'invoice.created' => ['aged_receivables', 'outstanding_balances', 'student_finance_*'],
        'invoice.updated' => ['aged_receivables', 'outstanding_balances', 'student_finance_*'],

        // Academic Events
        'grade.saved' => ['term_summary', 'class_performance', 'student_academic_*'],
        'attendance.marked' => ['attendance_report', 'student_academic_*'],

        // Library Events
        'book.borrowed' => ['book_circulation', 'overdue_books', 'student_library_*'],
        'book.returned' => ['book_circulation', 'overdue_books', 'student_library_*'],

        // Hostel Events
        'allocation.created' => ['room_occupancy', 'student_hostel_*'],
        'allocation.vacated' => ['room_occupancy', 'student_hostel_*'],

        // Inventory Events
        'inventory.issued' => ['stock_report', 'student_inventory_*'],
        'inventory.returned' => ['stock_report', 'student_inventory_*'],

        // Transport Events
        'transport.assigned' => ['route_students', 'student_transport_*'],
        'transport.unassigned' => ['route_students', 'student_transport_*'],
    ],
];
```

---

## Part 3: Development Phases

### Phase 1: Core Infrastructure (Week 1-2)

- [ ] Create module folder structure.
- [ ] Define core contracts (interfaces).
- [ ] Implement `ReportBuilder` service.
- [ ] Implement `DateRangeFactory` with presets (Today, This Week, This Month, This Term, Custom).
- [ ] Implement `FilterEngine` for dynamic filter application.
- [ ] Create database migrations.
- [ ] Set up basic routing.
- [ ] Write unit tests for core services.

### Phase 2: Embedded Reports Framework (Week 2-3)

- [ ] Implement `EntityViewController` for entity views.
- [ ] Create `EmbeddedReportInterface` contract.
- [ ] Implement tab registry system.
- [ ] Create sample embedded report (StudentFinanceTab).
- [ ] Implement summary widget system.
- [ ] Create reusable view components.
- [ ] Write feature tests for embedded reports.

### Phase 3: Module Integration (Week 3-5)

- [ ] Implement Finance embedded reports (Student, Parent, Class).
- [ ] Implement Academic embedded reports.
- [ ] Implement Library embedded reports.
- [ ] Implement Transport embedded reports.
- [ ] Implement Hostel embedded reports.
- [ ] Implement Inventory embedded reports.
- [ ] Create standalone Finance reports (Aged Receivables, Fee Collection).
- [ ] Create standalone Academic reports (Term Summary, Attendance).

### Phase 4: Advanced Features (Week 5-7)

- [ ] Implement `ComparisonEngine` for period-over-period.
- [ ] Implement `ExportService` with PDF and Excel.
- [ ] Implement `SchedulerService` for automated reports.
- [ ] Implement `SnapshotService` for official documents.
- [ ] Create async job processing for large reports.
- [ ] Implement rate limiting for exports.
- [ ] Add caching with invalidation.

### Phase 5: Polish & Documentation (Week 7-8)

- [ ] Create web dashboard UI.
- [ ] Add localization (English and Swahili).
- [ ] Performance optimization.
- [ ] Security audit (tenant isolation, permissions).
- [ ] Complete API documentation.
- [ ] Write developer guide.
- [ ] Create integration checklist for other modules.

---

## Part 4: Architectural Safeguards (Senior Architect Review)

### 4.1 Tenant Isolation (Critical)

**Risk**: Cross-school data leakage in multi-tenant environment.

**Mandate**: ALL queries MUST apply school_id scope. Use the `TenantScope` trait.

```php
// BAD - Missing tenant filter
$builder->select('*')->from('finance_invoices');

// GOOD - Always scope to tenant
$builder->select('*')
    ->from('finance_invoices')
    ->where('school_id', $this->getCurrentSchoolId());
```

### 4.2 Permission Enforcement for Drill-Down

**Risk**: User clicks drill-down link without permission to view target.

**Mandate**: Validate permissions before generating drill-down URLs.

```php
public function getDrillDownRoute(array $row): ?string
{
    // Check permission before providing drill-down
    if (!$this->authService->can('students.view')) {
        return null;
    }

    return route_to('students.show', $row['student_id']);
}
```

### 4.3 Rate Limiting for Exports

**Risk**: Denial of service via repeated expensive export requests.

**Mandate**: Apply rate limits to export endpoints.

```php
// Filters/ReportRateLimiter.php
public function before(RequestInterface $request, $arguments = null)
{
    $userId = auth()->id();
    $key = "report_export:{$userId}";

    if ($this->rateLimiter->tooManyAttempts($key, maxAttempts: 10, decayMinutes: 1)) {
        return $this->response->setStatusCode(429)
            ->setJSON(['error' => 'Too many export requests. Please wait.']);
    }

    $this->rateLimiter->hit($key);
}
```

### 4.4 Large Report Handling

**Risk**: Server timeout or memory exhaustion for large reports.

**Mandate**: Reports exceeding threshold must use async queue.

```php
public function generate(string $reportKey, array $filters): array
{
    $countQuery = $this->buildCountQuery($reportKey, $filters);
    $rowCount = $countQuery->countAllResults();

    if ($rowCount > self::ASYNC_THRESHOLD) {
        // Queue for background processing
        $jobId = $this->queueService->dispatch(
            new GenerateReportJob($reportKey, $filters, auth()->id())
        );

        return [
            'status' => 'queued',
            'job_id' => $jobId,
            'estimated_rows' => $rowCount,
            'message' => 'Report is being generated. You will be notified when ready.',
        ];
    }

    // Generate synchronously
    return $this->generateSync($reportKey, $filters);
}
```

---

## Part 5: Test Data Strategy

### 5.1 Seeding Strategy

Use `Modules\Reports\Database\Seeds\ReportTestSeeder` to populate realistic test data:

#### Finance Data

- 4 Schools with different enrollment sizes.
- 100 Students per school with varied fee statuses.
- Mix of paid, partial, and overdue invoices.
- Payment history spanning 6 months.

#### Academic Data

- Multiple terms with grade data.
- Attendance records with absences.
- Subject-wise performance data.

#### Library Data

- Book catalog with borrowing history.
- Mix of returned and overdue books.

### 5.2 Testing Scenarios

| Scenario | Expected Outcome |
|:---------|:-----------------|
| Generate Aged Receivables for School A | Returns only School A invoices |
| Export 5000-row report | Async job created, job_id returned |
| Student 360 for Student #1 | Combined data from all modules |
| Filter by invalid date range | Validation error returned |
| Unauthorized drill-down attempt | 403 Forbidden response |

---

## Part 6: Integration with Other Modules

Each module that provides data for reports must implement:

### 6.1 Embedded Report Classes

Create tab classes in `Reports/Embedded/{EntityType}/`:

```php
// Example: Finance module providing StudentFinanceTab
namespace App\Modules\Reports\Reports\Embedded\Student;

class StudentFinanceTab implements EmbeddedReportInterface
{
    public function getKey(): string { return 'student_finance'; }
    public function getTabName(): string { return 'Finance'; }
    public function getTabIcon(): string { return 'fas fa-dollar-sign'; }
    public function getTabOrder(): int { return 2; }
    // ... implement remaining methods
}
```

### 6.2 Report Registration

Register reports in `Config/ReportRegistry.php` (see Section 2.5).

### 6.3 Cache Invalidation Events

Fire events when data changes to invalidate report caches:

```php
// In Finance module after payment creation
Events::trigger('payment.created', ['student_id' => $studentId]);
```

### 6.4 Aggregate Refresh Logic

Implement aggregate table updates for high-volume data:

```php
// Example: Update daily_fee_summaries after payment
class PaymentCreatedHandler
{
    public function handle(array $data): void
    {
        $this->aggregateService->refreshDailyFeeSummary(
            schoolId: $data['school_id'],
            date: $data['transaction_date']
        );
    }
}
```

---

## Entity Views Reference

| Entity | Available Tabs |
|:-------|:---------------|
| **Student** | Overview, Finance, Academic, Library, Transport, Inventory, Hostel, Threads |
| **Parent** | Overview, Children, Finance, Academic, Threads |
| **Staff** | Overview, HR, Payroll, Classes, Threads |
| **Class** | Overview, Students, Finance, Academic, Attendance, Threads |
| **Book** | Details, Availability, Borrowing History |
| **Inventory Item** | Details, Stock, Transactions, Issued To |
| **Room** | Details, Occupants, History, Finance |
| **Route** | Overview, Stops, Students, Schedule, Finance |
| **School** | Overview, Enrollment, Finance, Staff, Performance |

---

## Quick Reference: Report Development Checklist

- [ ] **Determine Type**: Standalone, Embedded, or Both?
- [ ] **Create Class**: Implement appropriate interface.
- [ ] **Register**: Add to `ReportRegistry.php`.
- [ ] **Apply Tenant Scope**: All queries use school_id filter.
- [ ] **Add Caching**: Use `CacheService` with appropriate TTL.
- [ ] **Add Invalidation**: Fire events on data changes.
- [ ] **Test**: Unit tests + Feature tests + Performance tests.
- [ ] **Document**: Update API documentation.
