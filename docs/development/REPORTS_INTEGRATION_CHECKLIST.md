# 📋 Reports Integration Checklist

**Version**: 1.0.0
**Last Updated**: 2025-11-27

This checklist ensures complete integration of the Reports module when developing new modules or features.

---

## Pre-Development

- [ ] Read [Reports Module Specification](../specs/08-REPORTS_SPEC.md)
- [ ] Read [Reports Development Guide](REPORTS.md)
- [ ] Identify which entity types your module affects (Student, Parent, Staff, Class, etc.)
- [ ] Determine if you need Standalone reports, Embedded reports, or both
- [ ] Review existing reports for patterns and consistency

---

## Embedded Reports (Entity View Tabs)

### For Student Entity

- [ ] **Finance Tab** (`StudentFinanceTab`)
  - [ ] Fee balance summary widget
  - [ ] Invoice list with pagination
  - [ ] Payment history
  - [ ] Link to full Aged Receivables report

- [ ] **Academic Tab** (`StudentAcademicTab`)
  - [ ] Current term grades widget
  - [ ] Grade history by subject
  - [ ] Attendance summary
  - [ ] Link to full Academic report

- [ ] **Library Tab** (`StudentLibraryTab`)
  - [ ] Currently borrowed books widget
  - [ ] Borrowing history
  - [ ] Overdue alerts
  - [ ] Link to Book Circulation report

- [ ] **Transport Tab** (`StudentTransportTab`)
  - [ ] Current route assignment
  - [ ] Pickup/drop-off details
  - [ ] Fee status
  - [ ] Link to Route report

- [ ] **Inventory Tab** (`StudentInventoryTab`)
  - [ ] Items issued to student
  - [ ] Pending confirmations
  - [ ] Returned items history

- [ ] **Hostel Tab** (`StudentHostelTab`)
  - [ ] Current room allocation
  - [ ] Room history
  - [ ] Fee status

- [ ] **Threads Tab** (`StudentThreadsTab`)
  - [ ] Recent messages
  - [ ] Unread count widget
  - [ ] Link to full conversation

### For Parent Entity

- [ ] **Overview Tab** (`ParentOverviewTab`)
  - [ ] Children count widget
  - [ ] Quick access to each child

- [ ] **Children Tab** (`ParentChildrenTab`)
  - [ ] List of all children
  - [ ] Per-child summary (balance, grades, attendance)

- [ ] **Finance Tab** (`ParentFinanceTab`)
  - [ ] Combined balance for all children
  - [ ] Payment history
  - [ ] Outstanding invoices

- [ ] **Academic Tab** (`ParentAcademicTab`)
  - [ ] Combined academic summary
  - [ ] Per-child performance

### For Staff Entity

- [ ] **Overview Tab** (`StaffOverviewTab`)
  - [ ] Basic info and role

- [ ] **HR Tab** (`StaffHRTab`)
  - [ ] Employment details
  - [ ] Leave balance
  - [ ] Attendance

- [ ] **Payroll Tab** (`StaffPayrollTab`)
  - [ ] Salary history
  - [ ] Deductions
  - [ ] Pay slips

- [ ] **Classes Tab** (`StaffClassesTab`)
  - [ ] Assigned classes (for teachers)
  - [ ] Student count per class

### For Class Entity

- [ ] **Overview Tab** (`ClassOverviewTab`)
  - [ ] Student count
  - [ ] Teacher assignment

- [ ] **Students Tab** (`ClassStudentsTab`)
  - [ ] Student list with basic info
  - [ ] Quick actions

- [ ] **Finance Tab** (`ClassFinanceTab`)
  - [ ] Collection rate widget
  - [ ] Outstanding balances by student

- [ ] **Academic Tab** (`ClassAcademicTab`)
  - [ ] Class average by subject
  - [ ] Performance distribution

- [ ] **Attendance Tab** (`ClassAttendanceTab`)
  - [ ] Daily attendance rate
  - [ ] Absentee patterns

### For Book Entity (Library)

- [ ] **Details Tab** (`BookDetailsTab`)
  - [ ] Book information
  - [ ] Available copies widget

- [ ] **Availability Tab** (`BookAvailabilityTab`)
  - [ ] Copy status (available, borrowed, reserved)
  - [ ] Location

- [ ] **History Tab** (`BookHistoryTab`)
  - [ ] Borrowing history
  - [ ] Popular times

### For Inventory Item Entity

- [ ] **Details Tab** (`ItemDetailsTab`)
  - [ ] Item information
  - [ ] Category, SKU

- [ ] **Stock Tab** (`ItemStockTab`)
  - [ ] Stock by location
  - [ ] Reorder alerts

- [ ] **Transactions Tab** (`ItemTransactionsTab`)
  - [ ] Transfer history
  - [ ] Issue/return history

- [ ] **Issued To Tab** (`ItemIssuedToTab`)
  - [ ] Current assignments
  - [ ] Pending confirmations

### For Room Entity (Hostel)

- [ ] **Details Tab** (`RoomDetailsTab`)
  - [ ] Room info, capacity, cost

- [ ] **Occupants Tab** (`RoomOccupantsTab`)
  - [ ] Current occupants
  - [ ] Bed assignments

- [ ] **History Tab** (`RoomHistoryTab`)
  - [ ] Past allocations
  - [ ] Maintenance requests

- [ ] **Finance Tab** (`RoomFinanceTab`)
  - [ ] Revenue from this room
  - [ ] Outstanding balances

### For Route Entity (Transport)

- [ ] **Overview Tab** (`RouteOverviewTab`)
  - [ ] Route info, driver, vehicle

- [ ] **Stops Tab** (`RouteStopsTab`)
  - [ ] Stop list with times
  - [ ] Student count per stop

- [ ] **Students Tab** (`RouteStudentsTab`)
  - [ ] Assigned students
  - [ ] Pickup/drop-off status

- [ ] **Schedule Tab** (`RouteScheduleTab`)
  - [ ] Daily/weekly schedule
  - [ ] Trip history

- [ ] **Finance Tab** (`RouteFinanceTab`)
  - [ ] Revenue from this route
  - [ ] Collection rate

### For School Entity

- [ ] **Overview Tab** (`SchoolOverviewTab`)
  - [ ] Key metrics dashboard

- [ ] **Enrollment Tab** (`SchoolEnrollmentTab`)
  - [ ] Student count by class
  - [ ] Enrollment trends

- [ ] **Finance Tab** (`SchoolFinanceTab`)
  - [ ] Total revenue
  - [ ] Outstanding balances
  - [ ] Collection rate

- [ ] **Staff Tab** (`SchoolStaffTab`)
  - [ ] Staff count by role
  - [ ] Teacher-student ratio

- [ ] **Performance Tab** (`SchoolPerformanceTab`)
  - [ ] Academic performance summary
  - [ ] Comparison with previous terms

---

## Standalone Reports

### Finance Reports

- [ ] **Aged Receivables Report**
  - [ ] Filters: Date range, Class, Aging bucket, Min balance
  - [ ] Columns: Student, Admission No, Class, Invoiced, Paid, Balance, Days Overdue
  - [ ] Drill-down to student
  - [ ] Export: PDF, Excel

- [ ] **Fee Collection Report**
  - [ ] Filters: Date range, Payment method, Class
  - [ ] Columns: Date, Student, Amount, Method, Reference
  - [ ] Grouping: By day, week, month
  - [ ] Comparison: This period vs previous

- [ ] **Outstanding Balances Report**
  - [ ] Filters: Class, Min balance
  - [ ] Columns: Student, Total invoiced, Paid, Balance
  - [ ] Export with parent phone for follow-up

### Academic Reports

- [ ] **Term Summary Report**
  - [ ] Filters: Term, Class, Subject
  - [ ] Metrics: Average, Pass rate, Top performers
  - [ ] Comparison: This term vs last term

- [ ] **Attendance Report**
  - [ ] Filters: Date range, Class
  - [ ] Metrics: Attendance rate, Absences by student
  - [ ] Export for parent communication

### Library Reports

- [ ] **Book Circulation Report**
  - [ ] Filters: Date range, Category
  - [ ] Metrics: Books borrowed, Returned, Overdue
  - [ ] Top borrowed books

- [ ] **Overdue Books Report**
  - [ ] Filters: Days overdue
  - [ ] Columns: Book, Borrower, Due date, Days overdue
  - [ ] Export for follow-up

### HR Reports

- [ ] **Staff Directory Report**
  - [ ] Filters: Role, Department
  - [ ] Columns: Name, Role, Contact, Join date

- [ ] **Payroll Summary Report**
  - [ ] Filters: Month, Department
  - [ ] Metrics: Total salary, Deductions, Net pay

### Cross-Module Reports

- [ ] **Student 360 Report**
  - [ ] Combined: Finance + Academic + Library + Transport + Hostel
  - [ ] Single page per student
  - [ ] Export for parent meetings

- [ ] **School Dashboard Report**
  - [ ] All key metrics in one view
  - [ ] Trends and comparisons
  - [ ] Board-ready presentation format

---

## Data Infrastructure

### Aggregate Tables

- [ ] **daily_fee_summaries** - Pre-computed daily finance totals
  - [ ] Refreshed on payment.created, invoice.created events
  - [ ] Indexed by school_id, date

- [ ] **daily_attendance_summaries** - Pre-computed attendance rates
  - [ ] Refreshed on attendance.marked event
  - [ ] Indexed by school_id, class_id, date

- [ ] **term_grade_summaries** - Pre-computed academic averages
  - [ ] Refreshed on grade.saved event
  - [ ] Indexed by school_id, term_id, class_id

### Cache Invalidation

- [ ] Fire events when data changes:
  - [ ] `payment.created`, `payment.updated` → Finance reports
  - [ ] `invoice.created`, `invoice.updated` → Finance reports
  - [ ] `grade.saved` → Academic reports
  - [ ] `attendance.marked` → Attendance reports
  - [ ] `book.borrowed`, `book.returned` → Library reports
  - [ ] `allocation.created`, `allocation.vacated` → Hostel reports
  - [ ] `inventory.issued`, `inventory.returned` → Inventory reports
  - [ ] `transport.assigned`, `transport.unassigned` → Transport reports

- [ ] Subscribe to events in `ReportCacheInvalidationHandler`:
  ```php
  Events::on('payment.created', function ($data) {
      $this->cacheService->invalidate("student_finance_{$data['student_id']}");
      $this->cacheService->invalidate('aged_receivables');
      $this->cacheService->invalidate('fee_collection');
  });
  ```

---

## Testing

### Unit Tests

- [ ] Test each embedded report class:
  - [ ] `getKey()` returns expected value
  - [ ] `getTabName()` returns localized string
  - [ ] `isVisibleFor()` respects permissions
  - [ ] `getSummaryWidget()` returns correct structure
  - [ ] `getTabContent()` returns paginated data

- [ ] Test each standalone report class:
  - [ ] `buildQuery()` applies all filters correctly
  - [ ] `mapRow()` transforms data correctly
  - [ ] `getDrillDownRoute()` checks permissions

### Feature Tests

- [ ] Test API endpoints:
  - [ ] `GET /api/v1/entities/{type}/{id}/tabs` returns available tabs
  - [ ] `GET /api/v1/entities/{type}/{id}/tabs/{tab}` returns tab content
  - [ ] `POST /api/v1/reports/{key}` generates report
  - [ ] `POST /api/v1/reports/{key}/export` creates export

- [ ] Test tenant isolation:
  - [ ] Reports only return data for current school
  - [ ] Cannot access other schools' reports via drill-down

- [ ] Test permissions:
  - [ ] Unauthorized users cannot view restricted tabs
  - [ ] Admin can view all reports
  - [ ] Parent can only see their children's data

### Performance Tests

- [ ] Test with realistic data volume:
  - [ ] 10,000 students
  - [ ] 100,000 invoices
  - [ ] 500,000 payments

- [ ] Verify response times:
  - [ ] Embedded tab: < 500ms
  - [ ] Standalone report: < 2s
  - [ ] Large export: Queued (< 100ms response)

---

## Documentation

- [ ] Update API documentation for new endpoints
- [ ] Add report to catalog in `ReportRegistry.php`
- [ ] Document filters and columns in report class
- [ ] Add usage examples to developer guide
- [ ] Update entity views reference table if needed

---

## Final Review

### Code Quality

- [ ] All code follows PSR-12 standards
- [ ] Strict typing enabled (`declare(strict_types=1)`)
- [ ] No hard-coded strings (use language files)
- [ ] Proper error handling

### Security

- [ ] Tenant isolation verified (school_id filtering)
- [ ] Permission checks in place
- [ ] Input validation on filters
- [ ] Rate limiting on exports
- [ ] No SQL injection vulnerabilities

### Performance

- [ ] Indexes on filtered columns
- [ ] Aggregate tables used for large datasets
- [ ] Caching implemented with correct TTL
- [ ] Large exports use async queue

### Integration

- [ ] Events fired for cache invalidation
- [ ] Registered in `ReportRegistry.php`
- [ ] Routes added (API and Web)
- [ ] Language files updated

---

## Quick Reference: Files to Create/Update

### New Embedded Report

1. `app/Modules/Reports/Reports/Embedded/{Entity}/{Report}Tab.php` - Report class
2. `app/Modules/Reports/Config/ReportRegistry.php` - Register tab
3. `tests/Feature/Reports/{Report}TabTest.php` - Tests
4. `app/Language/en/Reports.php` - Strings

### New Standalone Report

1. `app/Modules/Reports/Reports/Standalone/{Module}/{Report}Report.php` - Report class
2. `app/Modules/Reports/Config/ReportRegistry.php` - Register report
3. `tests/Feature/Reports/{Report}ReportTest.php` - Tests
4. `app/Language/en/Reports.php` - Strings

### Cache Invalidation

1. `app/Modules/{YourModule}/Events/Handlers/{Event}Handler.php` - Fire events
2. `app/Modules/Reports/Events/ReportCacheInvalidationHandler.php` - Subscribe

---

## Related Documentation

- [Reports Module Specification](../specs/08-REPORTS_SPEC.md)
- [Reports Development Guide](REPORTS.md)
- [Module Development Guide](MODULES.md)
