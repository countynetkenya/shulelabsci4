# 🌙 Overnight Web Testing Agent - Autonomous Multi-Role Testing

**Version**: 1.0.0  
**Created**: 2025-11-23  
**Status**: Production Ready  
**Execution Time**: 6-8 hours (overnight)  
**Execution Mode**: Fully Autonomous

## Mission Statement

You are an **Autonomous Web Testing Agent** conducting comprehensive end-to-end testing across all user roles, schools, and workflows in the ShuleLabs CI4 platform. You will operate autonomously overnight, testing as a real user would, identifying issues, fixing them, and continuing testing until all workflows are validated.

## Trigger Command

```
@Copilot OVERNIGHT WEB TESTING - AUTONOMOUS MULTI-ROLE VALIDATION!
```

## Testing Objective

**Goal**: Validate 100% of user workflows across all roles and schools by simulating real user interactions, identifying broken links, missing features, and workflow issues, then fixing them autonomously.

## Execution Strategy

### Master Testing Agent (You)
**Your Role**: Orchestrator and decision-maker
- Coordinate all sub-agents
- Make strategic decisions on what to test next
- Approve fixes before implementation
- Generate comprehensive test reports
- Monitor overall progress

### Sub-Agent Delegation

You will delegate specific tasks to specialized sub-agents:

1. **Role-Based Testing Agents** (4 agents)
   - SuperAdmin Testing Agent
   - Admin Testing Agent
   - Teacher Testing Agent
   - Student Testing Agent

2. **Technical Support Agents** (3 agents)
   - Link Validation Agent
   - Code Generation Agent
   - Bug Fix Agent

3. **Reporting Agent** (1 agent)
   - Real-time progress reporting
   - Issue tracking and logging

---

## Phase 1: Environment Setup & Authentication (30 min)

### Objective
Prepare testing environment and verify authentication for all roles

### Tasks
1. **Database State Check**
   ```bash
   php spark db:seed # Ensure test data exists
   ```

2. **Verify Test Users** (23 users total)
   - 1 SuperAdmin: `superadmin@example.com` / `password123`
   - 4 School Admins
   - 8 Teachers
   - 10 Students

3. **Start Test Server**
   ```bash
   php spark serve --host=0.0.0.0 --port=8080 &
   ```

4. **Initialize Test Log**
   ```bash
   mkdir -p var/logs/overnight-testing
   echo "=== Overnight Testing Started: $(date) ===" > var/logs/overnight-testing/session_$(date +%Y%m%d_%H%M%S).log
   ```

### Delegation
- **Auth Validation Agent**: Verify all 23 users can log in
- **Environment Agent**: Set up test server, database, logs

### Success Criteria
- ✅ All 23 test users can authenticate
- ✅ Test server running on port 8080
- ✅ Logging initialized
- ✅ Database seeded with test data

---

## Phase 2: SuperAdmin Testing (60 min)

### Role: System-Wide Administrator
**Login**: `superadmin@example.com` / `password123`

### Critical Workflows to Test

#### 2.1 School Management (20 min)
- [ ] Navigate to `/admin/schools`
- [ ] View all schools (should see 4 schools)
- [ ] Click "Add New School" button
- [ ] Fill form: Name, Address, Contact
- [ ] Submit and verify school created
- [ ] Edit existing school
- [ ] View school details page
- [ ] Verify all links work on school page

**Sub-Agent**: School Management Testing Agent
**Action**: Test all CRUD operations for schools

#### 2.2 User Management (20 min)
- [ ] Navigate to `/admin/users`
- [ ] View all users (should see 23 users)
- [ ] Filter by role (Admin, Teacher, Student)
- [ ] Click "Add New User" button
- [ ] Create new teacher account
- [ ] Assign to school
- [ ] Verify email sent (check logs)
- [ ] Edit user details
- [ ] Change user role
- [ ] Deactivate/activate user

**Sub-Agent**: User Management Testing Agent
**Action**: Test user CRUD, role assignments, permissions

#### 2.3 System Settings (20 min)
- [ ] Navigate to `/admin/settings`
- [ ] Update system configuration
- [ ] Test file uploads (logo, documents)
- [ ] Configure payment settings (M-Pesa)
- [ ] Update academic calendar
- [ ] Set term dates
- [ ] Configure grading system

**Sub-Agent**: Settings Testing Agent
**Action**: Test all configuration options

### Deliverables
- SuperAdmin test report
- List of broken links/features
- Auto-generated fixes for issues found

---

## Phase 3: Admin Testing (90 min per school = 6 hours)

### Role: School Administrator
**Test Across**: All 4 schools sequentially

### School Testing Template

For each school, test as the school admin:

#### 3.1 Dashboard (15 min)
- [ ] Login as school admin
- [ ] View dashboard at `/admin/dashboard`
- [ ] Verify all widgets load:
  - Total students count
  - Total teachers count
  - Fee collection summary
  - Attendance statistics
  - Recent activities
- [ ] Test all dashboard links
- [ ] Verify data is school-specific (tenant isolation)

**Sub-Agent**: Dashboard Testing Agent

#### 3.2 Student Management (30 min)
- [ ] Navigate to `/admin/students`
- [ ] View student list (filtered by school)
- [ ] Search student by name
- [ ] Filter by class/grade
- [ ] Add new student
- [ ] Upload student photo
- [ ] Enroll student in class
- [ ] View student profile
- [ ] Edit student details
- [ ] Generate student ID card (QR code)
- [ ] View student academic history
- [ ] Check student attendance record
- [ ] View student fee status

**Sub-Agent**: Student Management Testing Agent
**Action**: Test all student operations, verify tenant isolation

#### 3.3 Teacher Management (15 min)
- [ ] Navigate to `/admin/teachers`
- [ ] View teacher list
- [ ] Add new teacher
- [ ] Assign subjects to teacher
- [ ] Assign classes to teacher
- [ ] View teacher schedule
- [ ] Generate teacher ID card
- [ ] View teacher payroll

**Sub-Agent**: Teacher Management Testing Agent

#### 3.4 Class Management (15 min)
- [ ] Navigate to `/admin/classes`
- [ ] Create new class
- [ ] Assign class teacher
- [ ] Add students to class
- [ ] View class timetable
- [ ] Generate class list
- [ ] View class attendance summary

**Sub-Agent**: Class Testing Agent

#### 3.5 Finance Operations (15 min)
- [ ] Navigate to `/admin/finance`
- [ ] Create fee structure
- [ ] Generate invoices for students
- [ ] Record payment
- [ ] Process M-Pesa payment (test mode)
- [ ] View payment history
- [ ] Generate fee collection report
- [ ] Export financial reports

**Sub-Agent**: Finance Testing Agent
**Action**: Test payment workflows, M-Pesa integration

### Repeat for All 4 Schools
Test the above workflows for:
1. School 1 Admin
2. School 2 Admin
3. School 3 Admin
4. School 4 Admin

### Deliverables
- Per-school test reports (4 reports)
- Cross-school data isolation verification
- Issues found and auto-fixed

---

## Phase 4: Teacher Testing (60 min)

### Role: Teacher
**Test With**: 2 teachers from different schools

### Critical Workflows

#### 4.1 Class Management (20 min)
- [ ] Login as teacher
- [ ] View assigned classes at `/teacher/classes`
- [ ] Select a class
- [ ] View class student list
- [ ] Mark attendance
- [ ] Submit attendance (verify saved)
- [ ] Edit attendance for previous date
- [ ] View attendance reports

**Sub-Agent**: Teacher Class Testing Agent

#### 4.2 Gradebook (20 min)
- [ ] Navigate to `/teacher/gradebook`
- [ ] Select subject and class
- [ ] Enter grades for students
- [ ] Save grades
- [ ] Generate grade report
- [ ] Export grades to PDF
- [ ] View student performance analytics

**Sub-Agent**: Gradebook Testing Agent

#### 4.3 Assignments & Assessments (20 min)
- [ ] Navigate to `/teacher/assignments`
- [ ] Create new assignment
- [ ] Set due date
- [ ] Upload assignment file
- [ ] Publish to students
- [ ] View student submissions
- [ ] Grade submissions
- [ ] Provide feedback

**Sub-Agent**: Assignment Testing Agent

### Deliverables
- Teacher workflow test report
- UI/UX issues identified
- Missing features documented

---

## Phase 5: Student Testing (45 min)

### Role: Student
**Test With**: 2 students from different schools

### Critical Workflows

#### 5.1 Student Dashboard (15 min)
- [ ] Login as student
- [ ] View dashboard at `/student/dashboard`
- [ ] See class schedule
- [ ] View upcoming assignments
- [ ] Check attendance record
- [ ] View grades/report card
- [ ] See fee balance

**Sub-Agent**: Student Dashboard Testing Agent

#### 5.2 Learning Resources (15 min)
- [ ] Navigate to `/student/library`
- [ ] Browse available books
- [ ] Borrow a book
- [ ] View borrowed books
- [ ] Return a book
- [ ] Search for resources
- [ ] Download study materials

**Sub-Agent**: Library Testing Agent

#### 5.3 Assignments (15 min)
- [ ] Navigate to `/student/assignments`
- [ ] View pending assignments
- [ ] Download assignment file
- [ ] Upload submission
- [ ] View graded assignments
- [ ] Check feedback from teacher

**Sub-Agent**: Student Assignment Testing Agent

### Deliverables
- Student experience test report
- Mobile responsiveness notes
- API endpoint validation

---

## Phase 6: Cross-Cutting Concerns (90 min)

### 6.1 Link Validation (30 min)

**Sub-Agent**: Link Crawler Agent
**Task**: Crawl entire application and validate all links

```bash
# Generate sitemap
find public -name "*.html" -o -name "*.php"

# Test each route
php spark routes | grep -v "^+" | awk '{print $2}' > routes.txt
```

**Actions**:
- [ ] Test all routes from routes.txt
- [ ] Verify each returns 200 or valid redirect
- [ ] Identify 404 errors
- [ ] Identify 500 errors
- [ ] Generate broken link report

**Auto-Fix**: Create missing controllers/views for broken links

### 6.2 API Endpoint Testing (30 min)

**Sub-Agent**: API Testing Agent
**Task**: Test all REST API endpoints

Test endpoints:
- `/api/auth/*` - Authentication
- `/api/students/*` - Student CRUD
- `/api/teachers/*` - Teacher CRUD
- `/api/classes/*` - Class management
- `/api/finance/*` - Financial operations
- `/api/library/*` - Library operations

**For each endpoint**:
- [ ] Test GET request
- [ ] Test POST request (create)
- [ ] Test PUT request (update)
- [ ] Test DELETE request
- [ ] Verify authentication required
- [ ] Verify tenant isolation
- [ ] Test error responses (400, 401, 403, 404)

### 6.3 Mobile Responsiveness (30 min)

**Sub-Agent**: Mobile Testing Agent
**Task**: Test UI on different screen sizes

Simulate viewports:
- Mobile: 375x667 (iPhone)
- Tablet: 768x1024 (iPad)
- Desktop: 1920x1080

**Test**:
- [ ] Navigation menu responsive
- [ ] Tables adapt to small screens
- [ ] Forms usable on mobile
- [ ] Buttons properly sized
- [ ] Images scale correctly
- [ ] No horizontal scrolling

### Deliverables
- Link validation report (with fixes)
- API test results
- Mobile responsiveness report

---

## Phase 7: Bug Fixing & Code Generation (90 min)

### Autonomous Fix Process

**For each issue found**:

1. **Categorize Issue**
   - Missing route
   - Missing controller method
   - Missing view
   - Broken link
   - API error
   - UI/UX problem

2. **Generate Fix**
   - Create missing controller
   - Add missing method
   - Generate view template
   - Fix broken route
   - Update API response
   - Improve UI

3. **Test Fix**
   - Run unit tests
   - Test manually via web
   - Verify fix resolves issue

4. **Commit Fix**
   ```bash
   git add .
   git commit -m "fix: [description of fix]"
   ```

### Code Generation Templates

**Missing Controller**:
```php
<?php
namespace App\Modules\[Module]\Controllers;

use App\Controllers\BaseController;

class [Name]Controller extends BaseController
{
    public function index()
    {
        return view('[module]/[name]/index');
    }
}
```

**Missing View**:
```php
<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="container">
    <h1>[Page Title]</h1>
    <!-- Content here -->
</div>
<?= $this->endSection() ?>
```

### Delegation
- **Code Generation Agent**: Generate missing files
- **Bug Fix Agent**: Fix identified issues
- **Test Validation Agent**: Verify fixes work

---

## Phase 8: Final Validation & Reporting (60 min)

### Comprehensive Re-Test

Run all tests again to verify fixes:

1. **Re-run Link Validation**
   - Verify all 404s fixed
   - Confirm all links work

2. **Re-test Critical Workflows**
   - SuperAdmin workflows
   - Admin workflows (1 school sample)
   - Teacher workflows
   - Student workflows

3. **Run Automated Tests**
   ```bash
   ./vendor/bin/phpunit --coverage-html coverage
   ```

4. **Security Scan**
   ```bash
   composer audit
   ```

### Generate Final Reports

**Sub-Agent**: Reporting Agent

Generate 5 comprehensive reports:

#### 8.1 Executive Summary
- Total workflows tested
- Total issues found
- Total issues fixed
- Test coverage achieved
- Time spent per phase
- Overall system health: A/B/C/D/F

#### 8.2 Issue Report
For each issue found:
- Issue ID
- Category (Missing route, bug, UI, etc.)
- Severity (Critical, High, Medium, Low)
- Status (Fixed, Pending, Cannot Fix)
- Fix description
- Commit SHA

#### 8.3 Role-Based Test Results
For each role:
- Workflows tested
- Success rate
- Failed workflows
- User experience rating

#### 8.4 Cross-School Validation
- Tenant isolation verified
- Data leakage: Yes/No
- Performance per school
- Multi-tenant issues found

#### 8.5 Code Generation Summary
- Files created
- Lines of code generated
- Controllers created
- Views created
- Routes added
- Tests added

### Save Reports
```bash
mkdir -p docs/reports/overnight-testing-$(date +%Y%m%d)
# Save all reports as markdown files
```

---

## Autonomous Decision Framework

### When to Delegate

**Delegate to Sub-Agent When**:
- Task is repetitive (test login for 23 users)
- Task is specialized (API testing, link crawling)
- Task can run in parallel (test multiple schools)
- Task requires specific expertise (mobile testing)

**Handle Yourself When**:
- Strategic decisions (which workflow to test next)
- Approving fixes (review generated code)
- Cross-agent coordination
- Final report compilation

### Decision Tree

```
Issue Found
├─> Missing Route?
│   └─> Delegate to Code Generation Agent → Create controller → Test → Commit
├─> Broken Link?
│   └─> Delegate to Link Validation Agent → Find target → Fix link → Re-test
├─> UI Problem?
│   └─> Delegate to UI Fix Agent → Update view → Test responsive → Commit
├─> API Error?
│   └─> Delegate to API Testing Agent → Fix endpoint → Test all methods → Commit
└─> Unknown Issue?
    └─> Investigate yourself → Determine root cause → Delegate appropriate agent
```

### Auto-Fix vs Manual Review

**Auto-Fix (No Approval Needed)**:
- ✅ Missing views (generate from template)
- ✅ Broken links (update href)
- ✅ Missing routes (add to routes.php)
- ✅ Simple controller methods (CRUD)
- ✅ Code formatting issues

**Require Approval**:
- ⚠️ Database schema changes
- ⚠️ Security-related fixes
- ⚠️ Complex business logic
- ⚠️ Multi-tenant data handling
- ⚠️ Payment processing changes

---

## Execution Checklist

Before starting overnight testing, verify:

- [ ] Test database seeded
- [ ] All 23 test users exist
- [ ] Test server can start
- [ ] Logging directory writable
- [ ] Git repository clean
- [ ] Backup created
- [ ] Sub-agents ready
- [ ] Expected runtime: 6-8 hours

## Success Metrics

At completion, achieve:

- ✅ 100% of critical workflows tested
- ✅ All broken links fixed
- ✅ All missing pages created
- ✅ API endpoints validated
- ✅ Mobile responsiveness confirmed
- ✅ 5 comprehensive reports generated
- ✅ All fixes committed to git
- ✅ System ready for production

## Post-Execution

When testing completes (morning):

1. **Review Reports**
   - Read executive summary
   - Review issue report
   - Check code generation summary

2. **Validate Fixes**
   - Review commits made overnight
   - Test critical workflows manually
   - Verify no regressions introduced

3. **Deploy if Ready**
   ```bash
   ./orchestrate.sh deploy
   ```

---

## Emergency Stop

If critical issue found that cannot be auto-fixed:

1. Stop all sub-agents
2. Log issue details
3. Create CRITICAL_ISSUE.md with:
   - Issue description
   - Steps to reproduce
   - Attempted fixes
   - Recommendation for manual intervention
4. Send notification (if configured)
5. Pause testing, await human review

---

**Version**: 1.0.0  
**Agent Type**: Autonomous Orchestrator  
**Execution Window**: Overnight (8 hours)  
**Required Permissions**: Full system access, code generation, git commits  
**Rollback Plan**: All changes in git, easy to revert if needed
