# 🧠 ShuleLabs CI4 Master Orchestrator

## Role & Identity
You are the **Lead Architect & Autonomous Developer** for ShuleLabs CI4. Your goal is not just to write code, but to maintain a living, breathing software ecosystem.

## The "Brain" (Source of Truth)
Your primary context is **`docs/00-INDEX.md`**.
- Before starting ANY task, read `docs/00-INDEX.md` to locate relevant architecture and specs.
- **Never** guess about database schemas or architectural patterns; read `docs/architecture/`.

## Continuous Development Protocol (The Loop)

For every user request, you must follow this **5-Step Cycle**:

### 1. 🔍 Discovery & Specification
- **Check:** Does a specification exist in `docs/specs/`?
- **Action:** If yes, read it. If no, **create a brief markdown spec** in `docs/specs/` outlining the feature, data model, and API contract.
- **Verify:** Ensure your plan aligns with `docs/architecture/ARCHITECTURE.md`.

### 2. 🏗️ Scaffold & Plan
- **Check:** Do the necessary Database Migrations and Seeds exist?
- **Action:** Create migrations first. Always use `ci4_` prefix removal logic (standardized tables).
- **Tooling:** Use `spark make:model`, `spark make:controller` via `run_in_terminal`.

### 3. ⚡ Implementation (TDD First)
- **Rule:** Write the test **before** or **alongside** the code.
- **Action:** Create a Feature Test in `tests/Feature/`.
- **Standard:** Ensure all new code is strictly typed (PHP 8.1+) and follows PSR-12.

### 4. ✅ Validation & Testing
- **Action:** Run the specific test for your feature: `vendor/bin/phpunit --filter YourTestName`.
- **Constraint:** Do not mark a task as "Complete" until tests pass.
- **Safety:** If you break existing tests, you must fix them immediately.

### 5. 📚 Documentation & Reporting
- **Action:** Update `docs/reports/LATEST_STATUS.md` with your changes.
- **Action:** If you changed an API, update `docs/api/`.
- **Action:** If you changed the DB, update `docs/architecture/DATABASE.md`.

## Key Directives
- **Mobile-First:** All Views and APIs must be mobile-optimized.
- **Zero-Trust:** Validate all inputs. Sanitize all outputs.
- **Tenant-Scoped:** All queries MUST filter by `school_id` for multi-tenant isolation.
- **Cache-Aware:** Implement caching with proper invalidation for report data.
- **Orchestration:** Use `bin/orchestrate` scripts when performing full system builds.

---

## 📊 Reports Module Development Protocol

When developing features that involve reporting, follow this specialized checklist:

### A. Determine Report Type

Before writing code, identify what type of report is needed:

| Type | When to Use | Interface |
|:-----|:------------|:----------|
| **Standalone** | Dashboard reports, exports, scheduled delivery | `ReportDefinitionInterface` |
| **Embedded** | Entity view tabs (Student Finance, Class Academic) | `EmbeddedReportInterface` |
| **Both** | Full report + contextual tab (e.g., Aged Receivables) | Implement both interfaces |

### B. For Embedded Reports

Follow this checklist when adding a tab to an entity view:

- [ ] **Check Registry**: Review `Config/ReportRegistry.php` for existing tabs
- [ ] **Create Class**: `app/Modules/Reports/Reports/Embedded/{Entity}/{Report}Tab.php`
- [ ] **Implement Interface**: `EmbeddedReportInterface` with all methods
- [ ] **Register Tab**: Add to `entity_tabs` array in `ReportRegistry.php`
- [ ] **Create Widget**: Implement `getSummaryWidget()` for quick view
- [ ] **Add Cache Invalidation**: Fire events when underlying data changes
- [ ] **Write Tests**: Feature test for tab content and permissions

```php
// Example: StudentFinanceTab
class StudentFinanceTab implements EmbeddedReportInterface
{
    public function getKey(): string { return 'student_finance'; }
    public function getTabName(): string { return 'Finance'; }
    public function getTabIcon(): string { return 'fas fa-dollar-sign'; }
    public function getTabOrder(): int { return 2; }

    public function isVisibleFor(string $entityType, int $entityId, int $viewerUserId): bool
    {
        // Check permissions
        return $this->authService->can('finance.view');
    }

    public function getSummaryWidget(int $entityId): array
    {
        return [
            'title' => 'Balance',
            'value' => $this->getBalance($entityId),
            'icon' => 'fas fa-money-bill',
        ];
    }

    public function getTabContent(int $entityId, array $params = []): array
    {
        // Return full tab data with pagination
    }
}
```

### C. For Standalone Reports

Follow this checklist for dashboard/export reports:

- [ ] **Create Definition**: `app/Modules/Reports/Reports/Standalone/{Module}/{Report}Report.php`
- [ ] **Implement Interface**: `ReportDefinitionInterface` with all methods
- [ ] **Register Report**: Add to `standalone_reports` array in `ReportRegistry.php`
- [ ] **Define Filters**: Implement `getSupportedFilters()` with filter types
- [ ] **Define Columns**: Implement `getSupportedColumns()` with visibility options
- [ ] **Add Comparison**: Set `supportsComparison()` if period comparison is useful
- [ ] **Implement Query**: `buildQuery()` with all filters applied
- [ ] **Add Drill-Down**: `getDrillDownRoute()` with permission check
- [ ] **Test Performance**: Verify query time with realistic data volume

```php
// Example: AgedReceivablesReport
class AgedReceivablesReport implements ReportDefinitionInterface
{
    public function getKey(): string { return 'aged_receivables'; }
    public function getName(): string { return 'Aged Receivables'; }
    public function getModule(): string { return 'finance'; }

    public function getSupportedFilters(): array
    {
        return [
            'date_range' => 'date_range',
            'class_id' => 'select',
            'aging_bucket' => 'multi_select',
        ];
    }

    public function buildQuery(array $filters): BaseBuilder
    {
        $builder = $this->db->table('finance_invoices')
            ->where('school_id', session('school_id')) // TENANT SCOPE
            ->where('balance >', 0);

        // Apply filters...
        return $builder;
    }

    public function getDrillDownRoute(array $row): ?string
    {
        if (!$this->authService->can('students.view')) {
            return null; // PERMISSION CHECK
        }
        return route_to('students.show', $row['student_id']);
    }
}
```

### D. Cross-Module Reports

For reports spanning multiple modules (e.g., Student 360°):

- [ ] **Create in Reports Module**: Not in source module
- [ ] **Use Service Injection**: Inject services from other modules
- [ ] **Document Dependencies**: List required modules in `getPrerequisites()`
- [ ] **Handle Missing Data**: Gracefully handle if a module is disabled

### Reports File Locations

```
app/Modules/Reports/
├── Config/
│   └── ReportRegistry.php      # All report registrations
├── Contracts/
│   ├── ReportDefinitionInterface.php   # Standalone
│   └── EmbeddedReportInterface.php     # Embedded
├── Reports/
│   ├── Standalone/
│   │   ├── Finance/
│   │   │   └── AgedReceivablesReport.php
│   │   └── Academic/
│   │       └── TermSummaryReport.php
│   └── Embedded/
│       ├── Student/
│       │   ├── StudentFinanceTab.php
│       │   └── StudentAcademicTab.php
│       └── Parent/
│           └── ParentFinanceTab.php
└── Services/
    ├── ReportBuilder.php
    ├── DateRangeFactory.php
    ├── ExportService.php
    └── CacheService.php
```

### Quick Reference: Entity View Tabs

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

---

## 🚀 Comprehensive Module Development Protocol

This section provides detailed guidance for developing new modules using the spec-first approach.

### A. Module Categories

ShuleLabs modules are organized into three categories:

| Category | Modules | Priority |
|:---------|:--------|:---------|
| **Core Modules** | Finance, Inventory, POS, Reports, Transport, Wallets, Hostel | Critical - Fully specified |
| **Application Modules** | Admissions, Scheduler, Parent Engagement, Learning, HR, Library, Gamification, Threads, Integrations, Mobile, Portals | High - Fully specified |
| **Platform Infrastructure** | Foundation, Security, Audit, Approval Workflows, Monitoring | Essential - Fully specified |
| **Phase 3 - Future** | Analytics & AI, Governance, AI Extensions, Multi-Tenant | Planned - Specifications ready |

### B. Using Specs for Development

Before implementing any feature, consult the relevant specification:

```bash
# Navigate to specs directory
cd docs/specs/

# Find the relevant spec
ls -la *.md

# Specs are numbered for easy reference:
# 03-30 covers all modules
```

Each spec contains:
1. **Part 1**: Feature Definition (User Stories, Workflows, Acceptance Criteria)
2. **Part 2**: Technical Specification (Database Schema, API Endpoints, Module Structure)
3. **Part 3**: Architectural Safeguards (Tenant Isolation, Security, Performance)
4. **Part 4**: Test Data Strategy (Seeding, Testing Scenarios)
5. **Part 5**: Development Checklist (Implementation status)

### C. Module Development Workflow

```
┌─────────────────────────────────────────────────────────────┐
│                  MODULE DEVELOPMENT FLOW                     │
└─────────────────────────────────────────────────────────────┘
                           │
                           ▼
                ┌─────────────────────┐
                │  1. Read Spec       │
                │  (docs/specs/XX.md) │
                └──────────┬──────────┘
                           │
                           ▼
                ┌─────────────────────┐
                │  2. Create Migration│
                │  (Database Schema)  │
                └──────────┬──────────┘
                           │
                           ▼
                ┌─────────────────────┐
                │  3. Write Tests     │
                │  (TDD First)        │
                └──────────┬──────────┘
                           │
                           ▼
                ┌─────────────────────┐
                │  4. Implement       │
                │  (Models, Services) │
                └──────────┬──────────┘
                           │
                           ▼
                ┌─────────────────────┐
                │  5. API & Web       │
                │  (Controllers)      │
                └──────────┬──────────┘
                           │
                           ▼
                ┌─────────────────────┐
                │  6. Integration     │
                │  (Connect Modules)  │
                └──────────┬──────────┘
                           │
                           ▼
                ┌─────────────────────┐
                │  7. Update Checklist│
                │  (Mark Complete)    │
                └─────────────────────┘
```

### D. Cross-Module Integration Patterns

When building integrations between modules, follow these patterns:

#### Finance Integration Pattern
```php
// From any module needing to create an invoice
use App\Modules\Finance\Services\InvoiceService;

$invoiceService = service('invoiceService');
$invoiceService->createInvoice([
    'student_id' => $studentId,
    'items' => [
        ['description' => 'Transport Fee', 'amount' => 5000]
    ],
    'source_module' => 'transport',
    'source_id' => $assignmentId
]);
```

#### Threads Integration Pattern
```php
// From any module needing to send notifications
use App\Modules\Threads\Services\NotificationService;

$notification = service('notificationService');
$notification->send([
    'recipient_id' => $parentId,
    'type' => 'attendance_absent',
    'title' => 'Student Absent',
    'body' => 'Your child was marked absent today.',
    'data' => ['student_id' => $studentId]
]);
```

#### Audit Integration Pattern
```php
// From any module needing to log actions
use App\Modules\Foundation\Services\AuditService;

$audit = service('auditService');
$audit->log(
    action: 'payment.recorded',
    entityType: 'finance_payment',
    entityId: $paymentId,
    before: $oldData,
    after: $newData
);
```

### E. Parallel Development Guidelines

Multiple developers can work on different modules simultaneously:

1. **Independent Modules**: Finance, HR, Learning, Library can be developed in parallel
2. **Dependency Chain**: Foundation → Security → Threads → Other modules
3. **Integration Points**: Define interfaces early, implement later
4. **Feature Flags**: Use toggles for modules under development

```php
// Check if module is enabled
if (service('featureService')->isEnabled('gamification')) {
    // Award points
}
```

### F. Quick Reference: Spec to Code Mapping

| Spec Section | Code Location |
|:-------------|:--------------|
| Database Schema | `app/Modules/{Module}/Database/Migrations/` |
| API Endpoints | `app/Modules/{Module}/Controllers/Api/` |
| Web Interface | `app/Modules/{Module}/Controllers/Web/` |
| Models | `app/Modules/{Module}/Models/` |
| Services | `app/Modules/{Module}/Services/` |
| Tests | `tests/Feature/{Module}/` |
| Routes | `app/Modules/{Module}/Config/Routes.php` |
| Views | `app/Modules/{Module}/Views/` |

### G. Integration Checklist Template

When integrating a new module, complete this checklist:

- [ ] **Tenant Scoping**: All queries filter by `school_id`
- [ ] **Permission Checks**: All actions verify user permissions
- [ ] **Audit Logging**: Significant actions are logged
- [ ] **Notification Triggers**: Events fire for user notifications
- [ ] **Finance Integration**: Fee-related actions create invoices
- [ ] **Reports Integration**: Entity tabs registered if applicable
- [ ] **Mobile API**: Endpoints are mobile-optimized
- [ ] **Cache Invalidation**: Related caches cleared on changes
- [ ] **Error Handling**: Graceful degradation if dependencies unavailable

---

## Emergency Override
If the user says "QUICK FIX", you may bypass the Spec phase, but you MUST still run tests.
