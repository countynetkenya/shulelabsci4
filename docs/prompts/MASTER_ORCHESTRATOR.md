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
- **Split-Brain Check:** Check if a legacy implementation exists in `app/Services/` or `app/Models/`. If a new Module exists in `app/Modules/`, **IGNORE** the legacy path and focus ONLY on the Module. Plan to deprecate the legacy code.

### 2. 🏗️ Scaffold & Plan
- **Check:** Do the necessary Database Migrations and Seeds exist?
- **Action:** Create migrations first. Always use `ci4_` prefix removal logic (standardized tables).
- **Validation:** Verify the *actual* database schema using `list_dir` on migrations or checking model definitions. Do not assume columns exist based on old specs. **CRITICAL: Read the migration file content before writing any Model or Service code.**
- **Tooling:** Use `spark make:model`, `spark make:controller` via `run_in_terminal`.

### 3. ⚡ Implementation (TDD First)
- **Rule:** Write the test **before** or **alongside** the code.
- **Action:** Create a Feature Test in `tests/Feature/` or `tests/Module/`.
- **Legacy Tests:** If refactoring a legacy module, **rewrite** the existing tests to point to the new Module classes. Do not leave broken legacy tests.
- **Standard:** Ensure all new code is strictly typed (PHP 8.1+) and follows PSR-12.

### 4. ✅ Validation & Testing
- **Action:** Run the specific test for your feature: `vendor/bin/phpunit --filter YourTestName`.
- **Constraint:** Do not mark a task as "Complete" until tests pass.
- **Safety:** If you break existing tests, you must fix them immediately.

### 5. 📚 Documentation & Reporting
- **Action:** Update `docs/reports/LATEST_STATUS.md` with your changes.
- **Action:** If you changed an API, update `docs/api/`.
- **Action:** If you changed the DB, update `docs/architecture/DATABASE.md`.
- **Archival:** If you replaced a legacy component, move the old documentation/specs to `docs/archive/` to prevent future confusion.

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

## 🎯 Master Development Orchestration Plan

Now that all specifications are complete, here is the comprehensive plan for parallel development with Copilot.

### Development Waves (Recommended Sequence)

Development should proceed in **5 waves**, with modules within each wave developed **in parallel**.

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    SHULE LABS DEVELOPMENT ORCHESTRATION                      │
│                         (Parallel Execution Plan)                            │
└─────────────────────────────────────────────────────────────────────────────┘

WAVE 1: FOUNDATION LAYER (Week 1-2) - Prerequisites for all modules
├── 22-FOUNDATION ─────────┐
├── 23-SECURITY ───────────┼── Run in PARALLEL (3 modules)
└── 24-AUDIT ──────────────┘
    │
    ▼ GATE: Core services functional

WAVE 2: CORE BUSINESS MODULES (Week 3-5) - Revenue & Operations
├── 03-FINANCE ────────────┐
├── 14-LEARNING ───────────┤
├── 15-HR ─────────────────┼── Run in PARALLEL (6 modules)
├── 09-TRANSPORT ──────────┤
├── 10-WALLETS ────────────┤
└── 05-HOSTEL ─────────────┘
    │
    ▼ GATE: Core business flows working

WAVE 3: SUPPORTING MODULES (Week 6-8) - Enhance core functionality
├── 06-INVENTORY ──────────┐
├── 07-POS ────────────────┤
├── 16-LIBRARY ────────────┼── Run in PARALLEL (7 modules)
├── 11-ADMISSIONS ─────────┤
├── 12-SCHEDULER ──────────┤
├── 18-THREADS ────────────┤
└── 19-INTEGRATIONS ───────┘
    │
    ▼ GATE: Integration points connected

WAVE 4: USER-FACING MODULES (Week 9-10) - Portals & Engagement
├── 20-MOBILE ─────────────┐
├── 21-PORTALS ────────────┼── Run in PARALLEL (5 modules)
├── 13-PARENT_ENGAGEMENT ──┤
├── 08-REPORTS ────────────┤
└── 25-APPROVAL_WORKFLOWS ─┘
    │
    ▼ GATE: User portals functional

WAVE 5: ENHANCEMENT MODULES (Week 11-12) - Polish & Future
├── 17-GAMIFICATION ───────┐
├── 26-MONITORING ─────────┼── Run in PARALLEL (6 modules)
├── 27-ANALYTICS_AI ───────┤
├── 28-GOVERNANCE ─────────┤
├── 29-AI_EXTENSIONS ──────┤
└── 30-MULTI_TENANT ───────┘

```

### Copilot Parallel Orchestration Commands

Use these commands to trigger parallel development:

#### Wave 1: Foundation (3 Parallel Tasks)
```
@Copilot WAVE 1 - FOUNDATION LAYER ORCHESTRATION

Execute these 3 modules IN PARALLEL:
1. Implement 22-FOUNDATION_SPEC.md (Tenant context, Audit service, Ledger, QR codes)
2. Implement 23-SECURITY_SPEC.md (Auth, RBAC, 2FA, Rate limiting)
3. Implement 24-AUDIT_SPEC.md (Event logging, Seals, Compliance)

Spec files: docs/specs/22-*, docs/specs/23-*, docs/specs/24-*
Follow TDD. Create migrations first. Run tests before marking complete.
```

#### Wave 2: Core Business (6 Parallel Tasks)
```
@Copilot WAVE 2 - CORE BUSINESS MODULES ORCHESTRATION

Execute these 6 modules IN PARALLEL:
1. Implement 03-FINANCE_SPEC.md (Invoices, Payments, Fee structures)
2. Implement 14-LEARNING_SPEC.md (Classes, Timetable, Gradebook)
3. Implement 15-HR_SPEC.md (Employees, Payroll, Leave)
4. Implement 09-TRANSPORT_SPEC.md (Vehicles, Routes, Tracking)
5. Implement 10-WALLETS_SPEC.md (Digital wallets, Top-ups, Limits)
6. Implement 05-HOSTEL_SPEC.md (Rooms, Allocations, Billing)

Spec files: docs/specs/03-*, docs/specs/14-*, etc.
Prerequisite: Wave 1 must be complete. Integrate with Foundation services.
```

#### Wave 3: Supporting Modules (7 Parallel Tasks)
```
@Copilot WAVE 3 - SUPPORTING MODULES ORCHESTRATION

Execute these 7 modules IN PARALLEL:
1. Implement 06-INVENTORY_SPEC.md (Stock, Transactions, Suppliers)
2. Implement 07-POS_SPEC.md (Sales, Payments, Receipts)
3. Implement 16-LIBRARY_SPEC.md (Books, Borrowing, Fines)
4. Implement 11-ADMISSIONS_SPEC.md (Applications, Interviews, Waitlist)
5. Implement 12-SCHEDULER_SPEC.md (Jobs, Cron, Retries)
6. Implement 18-THREADS_SPEC.md (Messaging, Announcements)
7. Implement 19-INTEGRATIONS_SPEC.md (M-Pesa, SMS, Email adapters)

Prerequisite: Wave 2 complete. Connect to Finance for billing.
```

#### Wave 4: User-Facing Modules (5 Parallel Tasks)
```
@Copilot WAVE 4 - USER-FACING MODULES ORCHESTRATION

Execute these 5 modules IN PARALLEL:
1. Implement 20-MOBILE_SPEC.md (JWT auth, Offline sync, Push tokens)
2. Implement 21-PORTALS_SPEC.md (Student portal, Parent portal)
3. Implement 13-PARENT_ENGAGEMENT_SPEC.md (Surveys, Events, Conferences)
4. Implement 08-REPORTS_SPEC.md (Standalone, Embedded, Export)
5. Implement 25-APPROVAL_WORKFLOWS_SPEC.md (Maker-checker, Routing)

Prerequisite: Waves 1-3 complete. All data sources available.
```

#### Wave 5: Enhancement Modules (6 Parallel Tasks)
```
@Copilot WAVE 5 - ENHANCEMENT MODULES ORCHESTRATION

Execute these 6 modules IN PARALLEL:
1. Implement 17-GAMIFICATION_SPEC.md (Points, Badges, Leaderboards)
2. Implement 26-MONITORING_SPEC.md (Health checks, Metrics, Tracing)
3. Implement 27-ANALYTICS_AI_SPEC.md (Predictive analytics, Dashboards)
4. Implement 28-GOVERNANCE_SPEC.md (Board meetings, Resolutions)
5. Implement 29-AI_EXTENSIONS_SPEC.md (Chatbots, NL queries)
6. Implement 30-MULTI_TENANT_SPEC.md (Tenant provisioning, Branding)

Prerequisite: Waves 1-4 complete. System fully functional.
```

### Single Module Development Command

For developing a single module:

```
@Copilot IMPLEMENT MODULE: [XX-MODULE_NAME]

Read specification: docs/specs/XX-MODULE_NAME_SPEC.md

Execute in order:
1. Create database migrations from spec Part 2.1
2. Run migrations: php spark migrate
3. Create models with relationships
4. Create services with business logic
5. Create API controllers per spec Part 2.2
6. Create web controllers and views
7. Write feature tests
8. Create seeders for test data
9. Run tests: vendor/bin/phpunit --filter ModuleName
10. Update development checklist in spec

Mark complete only when all tests pass.
```

### Module Dependency Matrix

| Module | Depends On | Blocks |
|:-------|:-----------|:-------|
| **Foundation** | None | Everything |
| **Security** | Foundation | All user-facing |
| **Audit** | Foundation | Compliance modules |
| **Finance** | Foundation, Security | Wallets, POS, Reports |
| **Learning** | Foundation, Security | Reports, Portals |
| **HR** | Foundation, Security | Payroll, Reports |
| **Transport** | Foundation, Finance | Mobile, Reports |
| **Wallets** | Foundation, Finance | POS, Mobile |
| **Hostel** | Foundation, Finance | Reports |
| **Inventory** | Foundation | POS |
| **POS** | Inventory, Wallets | Reports |
| **Library** | Foundation | Gamification |
| **Admissions** | Foundation, Finance | Reports |
| **Scheduler** | Foundation | All async tasks |
| **Threads** | Foundation | All notifications |
| **Integrations** | Foundation | Mobile, Portals |
| **Mobile** | All core modules | None |
| **Portals** | All core modules | None |
| **Reports** | All data modules | None |
| **Gamification** | Learning, Library | None |
| **Monitoring** | Foundation | None |
| **Analytics/AI** | All modules | None |

### Estimated Timeline

| Wave | Modules | Duration | Parallel Capacity |
|:-----|:--------|:---------|:-----------------|
| Wave 1 | 3 Foundation | 2 weeks | 3 concurrent |
| Wave 2 | 6 Core Business | 3 weeks | 6 concurrent |
| Wave 3 | 7 Supporting | 3 weeks | 7 concurrent |
| Wave 4 | 5 User-Facing | 2 weeks | 5 concurrent |
| Wave 5 | 6 Enhancement | 2 weeks | 6 concurrent |
| **Total** | **27 modules** | **12 weeks** | Up to 7 concurrent |

### Full System Orchestration Command

To trigger complete system development:

```
@Copilot FULL SYSTEM ORCHESTRATION - EXECUTE ALL WAVES

Begin ShuleLabs CI4 complete development:

Phase 1: Execute Wave 1 (Foundation)
Phase 2: On Wave 1 completion → Execute Wave 2 (Core Business)
Phase 3: On Wave 2 completion → Execute Wave 3 (Supporting)
Phase 4: On Wave 3 completion → Execute Wave 4 (User-Facing)
Phase 5: On Wave 4 completion → Execute Wave 5 (Enhancement)

For each module:
- Read spec from docs/specs/
- Execute TDD workflow
- Report progress after each module
- Run integration tests between waves

Final: Generate full system test report
```

---

## Emergency Override
If the user says "QUICK FIX", you may bypass the Spec phase, but you MUST still run tests.
