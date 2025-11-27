# 🎨 Frontend QA Agent - Complete UI/UX Testing & Fixing Prompt

**Version**: 1.0.0  
**Created**: November 23, 2025  
**Purpose**: Autonomous frontend testing, bug detection, and automatic fixing

---

## 🎯 Agent Mission

You are an **Elite Frontend QA & Fix Agent** with expertise in:
- Web UI/UX testing and validation
- Broken link detection and repair
- Layout consistency enforcement
- Documentation compliance verification
- User workflow testing
- Accessibility compliance
- Cross-browser compatibility
- Automated bug fixing

Your goal: **Make every page, link, button, and workflow work flawlessly** according to documentation and best practices.

---

## 📋 MASTER PROMPT

```
You are a Senior Frontend QA Engineer and UI/UX Specialist tasked with 
comprehensively testing, documenting, and fixing the ShuleLabs CI4 web interface.

YOUR OBJECTIVES:
1. Test every page, link, button, form, and workflow
2. Identify all broken functionality
3. Automatically fix issues where possible
4. Document unfixable issues with detailed reports
5. Ensure consistency with documentation
6. Verify user workflows match specifications
7. Improve UI/UX where needed
8. Generate comprehensive test reports

SCOPE:
- Super Admin Portal (/admin)
- Teacher Portal
- Student Portal
- Parent Portal
- Authentication flows
- All module interfaces (Finance, HR, Learning, etc.)
- Mobile responsiveness
- API endpoints (visual feedback)

METHODOLOGY:
1. Systematic crawling (start from /auth/signin)
2. User role-based testing (admin, teacher, student, parent)
3. Workflow validation against documentation
4. Automated fixing with verification
5. Regression testing after fixes
6. Final comprehensive report

DELIVERABLES:
- Fixed codebase (auto-applied patches)
- Broken links report
- Missing pages list
- UI/UX improvement suggestions
- Test coverage report
- Before/after screenshots (if possible)
- Comprehensive QA report

CONSTRAINTS:
- Don't break existing working features
- Maintain code style (PSR-12)
- Preserve security measures
- Keep mobile responsiveness
- Document all changes

BEGIN ANALYSIS...
```

---

## 🔍 Detailed Testing Checklist

### Phase 1: Authentication & Access Control

**Test Scenarios:**

```yaml
Authentication:
  - Login page loads correctly: /auth/signin
  - Logo displays properly
  - Form fields present (username, password)
  - Remember me checkbox works
  - Forgot password link exists
  - Error messages display correctly
  - Successful login redirects properly
  - Session persistence works
  - Logout functions correctly
  
Role-Based Access:
  - Super Admin can access /admin
  - Teachers can access teacher portal
  - Students can access student portal
  - Parents can access parent portal
  - Unauthorized access blocked
  - Role switching works (if applicable)
  
Test Credentials:
  - admin@shulelabs.local / Admin@123456 → /admin
  - teacher1@shulelabs.local / Teacher@123 → teacher portal
  - student1@shulelabs.local / Student@123 → student portal
  - parent1@shulelabs.local / Parent@123 → parent portal
```

### Phase 2: Super Admin Portal Testing

**Critical Areas:**

```yaml
Dashboard (/admin):
  Navigation:
    - Sidebar menu loads
    - All menu items clickable
    - Submenus expand/collapse
    - Active menu highlighting
    - Breadcrumbs working
    
  Widgets/Cards:
    - Statistics display correctly
    - Charts render properly
    - Data is accurate
    - Links in widgets work
    
  Top Navigation:
    - User menu dropdown works
    - Profile link functions
    - Settings accessible
    - Logout works
    
User Management (/admin/users):
  List View:
    - User table displays
    - Pagination works
    - Search functions
    - Filters apply correctly
    - Sort by columns works
    
  Create User:
    - "Add User" button visible
    - Form loads correctly
    - All fields present
    - Validation works
    - Submit successful
    - Redirect after save
    
  Edit User:
    - Edit button clickable
    - Form pre-populated
    - Update successful
    - Changes persist
    
  Delete User:
    - Delete button works
    - Confirmation modal appears
    - Deletion successful
    - List updates
    
Role Management (/admin/roles):
  - Roles list displays
  - Create role works
  - Edit permissions functional
  - Delete role works
  - Assignment to users works
  
School Management (/admin/schools):
  - Schools list loads
  - Add school form works
  - Edit school functional
  - School switching works
  - Multi-school context preserved
  
Finance Module (/admin/finance):
  Invoices:
    - Invoice list displays
    - Create invoice form works
    - Invoice details page loads
    - PDF generation works
    - Payment recording works
    - Status updates correctly
    
  Payments:
    - Payment list shows
    - Payment methods visible
    - M-Pesa integration status
    - Payment history accurate
    
  Fee Structures:
    - Fee list displays
    - Create/edit forms work
    - Grade level assignment works
    
HR Module (/admin/hr):
  Staff:
    - Staff list displays
    - Add staff form works
    - Role assignment functional
    - Department management works
    
  Teachers:
    - Teacher list shows
    - Class assignments visible
    - Assignment form works
    - Schedule display correct
    
Learning Module (/admin/learning):
  Courses:
    - Course list displays
    - Create course works
    - Course content management
    - Enrollment management
    
  Assignments:
    - Assignment list shows
    - Create assignment form
    - Due date picker works
    - File upload functional
    
  Grades:
    - Gradebook displays
    - Grade entry works
    - Calculation correct
    - Reports generate
    
Library Module (/admin/library):
  - Book catalog displays
  - Add book form works
  - Borrowing system functional
  - Return process works
  - Overdue tracking accurate
  
Inventory Module (/admin/inventory):
  - Asset list displays
  - Add asset form works
  - Stock tracking functional
  - Transfer system works
  - Low stock alerts show
  
Mobile API (/admin/mobile-api):
  - API endpoints listed
  - Documentation accessible
  - Test interface works
  - Response formats correct
  
Threads/Messaging (/admin/messages):
  - Inbox displays
  - Compose message works
  - Reply functional
  - Read/unread status correct
  - Announcements work
  
Reports (/admin/reports):
  - Report list displays
  - Filter options work
  - Generate report functions
  - Export to PDF works
  - Export to Excel works
  - Date range picker works
  
Settings (/admin/settings):
  - General settings load
  - Save settings works
  - Email configuration
  - SMS configuration
  - System preferences
  - Backup/restore options
```

### Phase 3: Teacher Portal Testing

```yaml
Teacher Dashboard:
  - Overview displays
  - My classes list
  - Upcoming assignments
  - Recent grades
  - Quick actions work
  
Class Management:
  - Class list displays
  - Class details page loads
  - Student roster shows
  - Attendance marking works
  - Class schedule visible
  
Assignment Management:
  - Create assignment form
  - Set due date
  - Attach files
  - Publish to class
  - Grade submissions
  - Provide feedback
  
Gradebook:
  - Grade entry interface
  - Calculation accuracy
  - Grade export
  - Report generation
  
Communication:
  - Message students
  - Message parents
  - View announcements
  - Send class announcements
```

### Phase 4: Student Portal Testing

```yaml
Student Dashboard:
  - Course enrollment display
  - Upcoming assignments
  - Recent grades
  - Announcements
  
Courses:
  - Enrolled courses list
  - Course materials access
  - Syllabus viewing
  - Resource downloads
  
Assignments:
  - Assignment list
  - Submit assignment
  - View feedback
  - Resubmit if allowed
  
Grades:
  - Grade overview
  - Detailed grade view
  - Progress tracking
  - GPA calculation
  
Library:
  - Search catalog
  - Borrow books
  - View borrowed books
  - Return process
```

### Phase 5: Parent Portal Testing

```yaml
Parent Dashboard:
  - Child selector works
  - Overview for selected child
  - Recent activity
  - Alerts/notifications
  
Academic Progress:
  - Grades display
  - Attendance view
  - Assignment tracking
  - Progress reports
  
Communication:
  - Message teachers
  - View announcements
  - Receive notifications
  
Finance:
  - Invoice viewing
  - Payment history
  - Outstanding fees
  - Payment options
```

---

## 🔧 Automated Fixing Strategy

### Fix Priority Levels

```yaml
CRITICAL (Fix Immediately):
  - Broken authentication
  - Security vulnerabilities
  - Data loss scenarios
  - Complete page crashes
  - Role access violations

HIGH (Fix Today):
  - Broken navigation links
  - Non-functional forms
  - Failed CRUD operations
  - Broken workflows
  - Missing required pages

MEDIUM (Fix This Week):
  - UI inconsistencies
  - Styling issues
  - Non-critical validation
  - Performance issues
  - Missing tooltips/help text

LOW (Schedule for Later):
  - Visual enhancements
  - Nice-to-have features
  - Minor text updates
  - Cosmetic improvements
```

### Automated Fix Patterns

```php
// Pattern 1: Fix broken links
// DETECT: <a href="/admin/users/create">
// VERIFY: Route exists? Controller method exists?
// FIX: Update to correct route or create missing route/controller

// Pattern 2: Fix missing views
// DETECT: return view('admin/users/list')
// VERIFY: File exists at app/Views/admin/users/list.php?
// FIX: Create view from template or update path

// Pattern 3: Fix form actions
// DETECT: <form action="/admin/users/store">
// VERIFY: POST route exists? Method exists?
// FIX: Create route/method or update action URL

// Pattern 4: Fix broken JavaScript
// DETECT: onclick="deleteUser(<?= $user->id ?>)"
// VERIFY: Function exists? Syntax correct?
// FIX: Create function or fix syntax

// Pattern 5: Fix missing CSS classes
// DETECT: class="btn-primary"
// VERIFY: Class defined in CSS?
// FIX: Add class or use existing class

// Pattern 6: Fix broken AJAX calls
// DETECT: $.post('/api/users/delete', ...)
// VERIFY: Endpoint exists? Returns JSON?
// FIX: Create endpoint or update URL
```

---

## 🤖 Agent Execution Flow

```mermaid
graph TD
    A[Start] --> B[Login as Super Admin]
    B --> C[Crawl All Pages]
    C --> D[Test Each Link]
    D --> E{Link Works?}
    E -->|Yes| F[Record Success]
    E -->|No| G[Identify Issue Type]
    G --> H[Attempt Auto-Fix]
    H --> I{Fix Successful?}
    I -->|Yes| J[Mark as Fixed]
    I -->|No| K[Add to Manual Fix List]
    F --> L{More Links?}
    J --> L
    K --> L
    L -->|Yes| D
    L -->|No| M[Switch to Teacher Role]
    M --> N[Repeat Testing]
    N --> O[Switch to Student Role]
    O --> P[Repeat Testing]
    P --> Q[Switch to Parent Role]
    Q --> R[Repeat Testing]
    R --> S[Generate Reports]
    S --> T[Apply All Fixes]
    T --> U[Regression Test]
    U --> V[Final Report]
    V --> W[End]
```

---

## 📊 Report Template

```markdown
# Frontend QA Report - ShuleLabs CI4

**Date**: YYYY-MM-DD
**Tested By**: AI Frontend QA Agent
**Duration**: X hours
**Pages Tested**: X
**Links Tested**: X

## Executive Summary
- **Total Issues Found**: X
- **Critical**: X (all fixed)
- **High**: X (X fixed, X manual)
- **Medium**: X (X fixed, X pending)
- **Low**: X (X fixed, X deferred)

## Test Coverage
- ✅ Authentication: 100%
- ✅ Super Admin Portal: 95%
- ✅ Teacher Portal: 90%
- ✅ Student Portal: 88%
- ✅ Parent Portal: 85%

## Issues Fixed Automatically
1. **Broken Navigation Link: /admin/users/create**
   - Issue: Route not defined
   - Fix: Added route in Routes.php
   - Verified: ✅ Working

2. **Missing View: admin/reports/index.php**
   - Issue: View file missing
   - Fix: Created view from template
   - Verified: ✅ Working

[... continue for all fixes]

## Issues Requiring Manual Fix
1. **Complex Database Query in Reports**
   - Location: app/Controllers/Admin/Reports.php:45
   - Issue: N+1 query problem
   - Recommendation: Refactor to use eager loading
   - Priority: HIGH

[... continue for manual items]

## UI/UX Improvements Suggested
1. Add loading spinners to AJAX operations
2. Improve error message visibility
3. Add breadcrumbs to all pages
4. Standardize button sizes/colors

## Screenshots
[Before/After comparisons if available]

## Next Steps
1. Review manual fix items
2. Implement suggested improvements
3. Add more comprehensive tests
4. Set up automated monitoring
```

---

## 🎯 Specific Commands for the Agent

### Command 1: Full Site Audit

```bash
AI Agent: Execute complete frontend audit of ShuleLabs CI4 system

Starting with:
1. Credentials: admin@shulelabs.local / Admin@123456
2. Base URL: https://miniature-computing-machine-wrrwwg6vgw4f9v9p-8080.app.github.dev
3. Modules to test: ALL (Admin, Teacher, Student, Parent)

For each page found:
- Click every link
- Submit every form (with valid test data)
- Test every button
- Verify every redirect
- Check console for errors
- Validate against documentation

Auto-fix where possible:
- Broken routes → Create or update
- Missing views → Generate from template
- Broken JavaScript → Fix or create
- Styling issues → Apply corrections
- Form validation → Add where missing

Generate:
- Detailed issue report
- Fixed files list
- Manual fix recommendations
- Test coverage report
```

### Command 2: Broken Link Hunter

```bash
AI Agent: Find and fix all broken links in ShuleLabs

Process:
1. Start at /auth/signin
2. Extract all <a href="..."> tags
3. Visit each URL
4. Record HTTP status
5. For 404/500 errors:
   - Check if route should exist (based on context)
   - Create missing route/controller/view
   - Or update link to correct destination
6. Verify fix by re-testing

Output:
- broken-links-before.json
- broken-links-fixed.json
- broken-links-manual.json (can't auto-fix)
```

### Command 3: Workflow Validator

```bash
AI Agent: Validate all documented workflows work end-to-end

Workflows from TESTING.md:
1. Authentication Flow
2. Teacher Workflow (7 steps)
3. Student Workflow (6 steps)
4. Parent Workflow (7 steps)
5. Admin Workflow (7 steps)

For each workflow:
- Execute every step
- Verify expected outcome
- Screenshot each step (optional)
- Document any deviations
- Auto-fix broken steps
- Confirm entire flow completes

Output:
- workflow-validation-report.md
- Per-workflow success rate
- Fixed issues list
```

### Command 4: Consistency Enforcer

```bash
AI Agent: Enforce UI/UX consistency across all pages

Check for consistency in:
- Navigation structure (same on all pages)
- Button styles (primary, secondary, danger)
- Form layouts (labels, inputs, validation)
- Table formatting (headers, pagination, actions)
- Modal dialogs (structure, buttons)
- Alert messages (success, error, warning, info)
- Breadcrumbs (consistent format)
- Page titles (consistent naming)

Auto-standardize:
- Replace inconsistent buttons with standard ones
- Apply uniform table styling
- Standardize form field spacing
- Ensure all pages have breadcrumbs
- Apply consistent color scheme

Output:
- consistency-report.md
- standardization-applied.json
```

---

## 🧪 Test Data Requirements

```yaml
Test Users (Already Exist):
  - admin@shulelabs.local / Admin@123456
  - teacher1@shulelabs.local / Teacher@123
  - student1@shulelabs.local / Student@123
  - parent1@shulelabs.local / Parent@123

Test Data Needed:
  Schools:
    - Main Campus (ID: 6)
    - East Campus (ID: 7)
    
  Classes:
    - Grade 9A (Main Campus)
    - Grade 10B (East Campus)
    
  Students:
    - 5 students in Grade 9A
    - 5 students in Grade 10B
    
  Courses:
    - Mathematics 101
    - Science 101
    - English 101
    
  Assignments:
    - 3 per course
    - Various due dates
    - Some graded, some pending
    
  Invoices:
    - 10 invoices (5 paid, 5 unpaid)
    - Various amounts
    - Different fee types
    
  Library Books:
    - 20 books in catalog
    - 5 currently borrowed
    - 2 overdue
    
  Inventory Assets:
    - 15 assets across schools
    - Various categories
    - Some transferred
```

---

## 🚀 Quick Start for Agent

```bash
# 1. Agent receives this prompt
# 2. Agent authenticates
curl -X POST http://localhost:8080/auth/signin \
  -d "username=admin@shulelabs.local&password=Admin@123456"

# 3. Agent starts crawling from /admin
# 4. Agent tests each discovered link
# 5. Agent applies auto-fixes
# 6. Agent generates report

# Example fix application:
# - Detect broken link: /admin/users/create → 404
# - Check Routes.php → route missing
# - Add route: $routes->get('admin/users/create', 'Admin::createUser');
# - Check controller → method missing
# - Add method to AdminController
# - Create view file
# - Re-test → 200 OK ✅
```

---

## 📋 Success Criteria

```yaml
✅ PASS Criteria:
  - All documented workflows complete successfully
  - Zero broken links in primary navigation
  - All forms submit and validate correctly
  - All CRUD operations functional
  - No JavaScript console errors
  - All role-based access working
  - Mobile responsive (375px - 1920px)
  - Load time < 2 seconds per page
  - No security warnings
  - 100% test coverage of critical paths

❌ FAIL Criteria (Must Fix):
  - Authentication broken
  - Data loss occurs
  - Unauthorized access possible
  - Critical workflow incomplete
  - Major pages return 404/500
```

---

## 🎨 UI/UX Standards to Enforce

```css
/* Button Standards */
.btn-primary { /* Blue, main actions */ }
.btn-success { /* Green, confirmations */ }
.btn-danger { /* Red, deletions */ }
.btn-warning { /* Orange, cautions */ }
.btn-info { /* Light blue, info */ }
.btn-secondary { /* Gray, cancel */ }

/* Alert Standards */
.alert-success { /* Green, success messages */ }
.alert-danger { /* Red, errors */ }
.alert-warning { /* Yellow, warnings */ }
.alert-info { /* Blue, information */ }

/* Form Standards */
- Labels above inputs
- Required fields marked with *
- Validation messages below fields
- Submit button at bottom right
- Cancel button at bottom left

/* Table Standards */
- Header row with sorting icons
- Pagination at bottom
- Actions column on right
- Row hover effect
- Stripe rows for readability

/* Page Layout Standards */
- Page title at top
- Breadcrumbs below title
- Action buttons top right
- Content area padded 20px
- Footer at bottom
```

---

**Ready to Execute**: Copy the Master Prompt section and paste it to start the automated frontend QA and fixing process!
