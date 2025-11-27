# 🎓 Admissions & CRM Module Specification

**Version**: 1.0.0
**Status**: Draft
**Last Updated**: 2025-11-27

---

## Part 1: Feature Definition (The "What" & "Why")
*Target Audience: Product Owners, Stakeholders, Developers*

### 1.1 Overview
The Admissions module is the "Front Door" of the school. It manages the complete student enrollment lifecycle from initial inquiry through application, evaluation, and final acceptance. It includes CRM (Customer Relationship Management) capabilities to track prospective families as leads and nurture them through the enrollment funnel.

### 1.2 User Stories

- **As an Admissions Officer**, I want to receive online applications with document uploads, so that parents can apply from anywhere.
- **As a Principal**, I want to schedule entrance tests and interviews, so that we can evaluate applicants.
- **As an Admissions Manager**, I want to manage a waitlist with auto-promotion, so that we can fill spots when they become available.
- **As a Parent**, I want to track my application status online, so that I know where I am in the process.
- **As a Marketing Officer**, I want to track leads from inquiries, so that I can follow up with interested families.
- **As an Admin**, I want to generate admission letters automatically, so that I can onboard accepted students quickly.

### 1.3 User Workflows

1. **Lead Capture**:
   - Parent visits school website or attends open day.
   - Parent fills inquiry form with contact details.
   - System creates a lead with status "New".
   - Admissions team follows up and updates lead status.

2. **Application Submission**:
   - Parent registers on the portal.
   - Parent fills application form with student details.
   - Parent uploads required documents (birth certificate, previous report cards).
   - System validates documents and creates application with status "Submitted".
   - Parent receives confirmation email/SMS.

3. **Application Review**:
   - Admissions officer reviews application.
   - Officer requests additional documents if needed.
   - Officer marks application as "Under Review" → "Reviewed".
   - Application moves to next stage (test/interview).

4. **Entrance Test**:
   - Admin schedules entrance test for batch of applicants.
   - Parents receive notification with test date/venue.
   - Test is conducted and scores are entered.
   - Application updated with test results.

5. **Interview**:
   - Admin schedules parent/student interview.
   - Interview panel conducts interview.
   - Panel submits interview notes and recommendation.
   - Application updated with interview outcome.

6. **Decision**:
   - Admissions committee reviews complete application.
   - Decision made: Accept, Reject, or Waitlist.
   - System generates admission letter (if accepted).
   - Parent notified via email/SMS/portal.

7. **Onboarding**:
   - Parent accepts offer and pays admission fee.
   - System creates student record in Learning module.
   - System creates parent account linked to student.
   - Onboarding checklist provided to parent.

### 1.4 Acceptance Criteria

- [ ] Parents can submit applications online with document uploads.
- [ ] Application workflow supports configurable stages.
- [ ] Entrance tests can be scheduled with batch management.
- [ ] Interviews can be scheduled with panel assignment.
- [ ] Waitlist automatically promotes when spots open.
- [ ] Admission letters are generated from templates.
- [ ] Lead CRM tracks inquiry sources and follow-ups.
- [ ] All data is tenant-scoped by school_id.
- [ ] Notifications sent at each stage transition.

---

## Part 2: Technical Specification (The "How")
*Target Audience: Developers, Architects*

### 2.1 Database Schema

#### `admission_leads`
Prospective family inquiries before formal application.
```sql
CREATE TABLE admission_leads (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    student_name VARCHAR(150),
    parent_name VARCHAR(150) NOT NULL,
    parent_email VARCHAR(255),
    parent_phone VARCHAR(20) NOT NULL,
    grade_applying_for VARCHAR(50),
    source ENUM('website', 'referral', 'open_day', 'advertisement', 'walk_in', 'other') DEFAULT 'website',
    source_detail VARCHAR(255),
    status ENUM('new', 'contacted', 'interested', 'applied', 'not_interested', 'lost') DEFAULT 'new',
    notes TEXT,
    assigned_to INT NULL,
    next_follow_up DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_school_status (school_id, status),
    INDEX idx_follow_up (next_follow_up)
);
```

#### `applications`
Formal student applications.
```sql
CREATE TABLE applications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    application_number VARCHAR(50) NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    lead_id INT NULL,
    
    -- Student Information
    student_first_name VARCHAR(100) NOT NULL,
    student_last_name VARCHAR(100) NOT NULL,
    student_gender ENUM('male', 'female', 'other') NOT NULL,
    student_dob DATE NOT NULL,
    student_nationality VARCHAR(100),
    student_photo_path VARCHAR(500),
    
    -- Application Details
    grade_applying_for VARCHAR(50) NOT NULL,
    previous_school VARCHAR(255),
    reason_for_transfer TEXT,
    special_needs TEXT,
    
    -- Parent/Guardian Information (JSON for flexibility)
    parent_details JSON NOT NULL,
    
    -- Status Tracking
    stage_id INT NOT NULL,
    status ENUM('draft', 'submitted', 'under_review', 'pending_test', 'pending_interview', 'waitlisted', 'accepted', 'rejected', 'withdrawn', 'enrolled') DEFAULT 'draft',
    submitted_at DATETIME,
    decision_at DATETIME,
    decision_by INT,
    decision_notes TEXT,
    
    -- Fee and Payment
    application_fee DECIMAL(10,2) DEFAULT 0,
    fee_paid BOOLEAN DEFAULT FALSE,
    fee_payment_reference VARCHAR(100),
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES admission_leads(id) ON DELETE SET NULL,
    FOREIGN KEY (stage_id) REFERENCES application_stages(id),
    FOREIGN KEY (decision_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uk_app_number (school_id, application_number),
    INDEX idx_school_status (school_id, status),
    INDEX idx_school_year (school_id, academic_year)
);
```

#### `application_stages`
Configurable workflow stages per school.
```sql
CREATE TABLE application_stages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50) NOT NULL,
    sequence INT NOT NULL,
    requires_test BOOLEAN DEFAULT FALSE,
    requires_interview BOOLEAN DEFAULT FALSE,
    requires_documents JSON,
    is_terminal BOOLEAN DEFAULT FALSE,
    next_stage_on_pass INT NULL,
    next_stage_on_fail INT NULL,
    notification_template_id INT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    UNIQUE KEY uk_school_code (school_id, code),
    INDEX idx_sequence (school_id, sequence)
);
```

#### `application_documents`
Uploaded documents for applications.
```sql
CREATE TABLE application_documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    application_id INT NOT NULL,
    document_type VARCHAR(100) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT,
    mime_type VARCHAR(100),
    verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    verified_by INT NULL,
    verified_at DATETIME,
    rejection_reason TEXT,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_app_type (application_id, document_type)
);
```

#### `entrance_tests`
Scheduled entrance examinations.
```sql
CREATE TABLE entrance_tests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    grade_level VARCHAR(50),
    test_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    venue VARCHAR(255),
    max_candidates INT,
    passing_score DECIMAL(5,2),
    instructions TEXT,
    status ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_school_date (school_id, test_date)
);
```

#### `entrance_test_results`
Individual candidate test results.
```sql
CREATE TABLE entrance_test_results (
    id INT PRIMARY KEY AUTO_INCREMENT,
    test_id INT NOT NULL,
    application_id INT NOT NULL,
    score DECIMAL(5,2),
    grade VARCHAR(10),
    passed BOOLEAN,
    remarks TEXT,
    evaluated_by INT,
    evaluated_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (test_id) REFERENCES entrance_tests(id) ON DELETE CASCADE,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    FOREIGN KEY (evaluated_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uk_test_app (test_id, application_id)
);
```

#### `interviews`
Parent/student interviews.
```sql
CREATE TABLE interviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    application_id INT NOT NULL,
    interview_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME,
    venue VARCHAR(255),
    interview_type ENUM('in_person', 'virtual') DEFAULT 'in_person',
    virtual_link VARCHAR(500),
    panel_members JSON,
    status ENUM('scheduled', 'completed', 'no_show', 'rescheduled', 'cancelled') DEFAULT 'scheduled',
    overall_rating INT,
    recommendation ENUM('strong_accept', 'accept', 'waitlist', 'reject'),
    notes TEXT,
    conducted_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    FOREIGN KEY (conducted_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_app (application_id),
    INDEX idx_date (interview_date)
);
```

#### `waitlists`
Waitlisted applications with priority ranking.
```sql
CREATE TABLE waitlists (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    grade_level VARCHAR(50) NOT NULL,
    application_id INT NOT NULL,
    priority INT NOT NULL,
    added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('waiting', 'offered', 'accepted', 'declined', 'expired') DEFAULT 'waiting',
    offer_sent_at DATETIME,
    offer_expires_at DATETIME,
    response_at DATETIME,
    notes TEXT,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    UNIQUE KEY uk_app_year_grade (application_id, academic_year, grade_level),
    INDEX idx_school_grade (school_id, grade_level, priority)
);
```

### 2.2 API Endpoints (Mobile-First)

| Method | Endpoint | Description | Access |
|:-------|:---------|:------------|:-------|
| **Leads** |
| GET | `/api/v1/admissions/leads` | List leads with filters | Staff |
| POST | `/api/v1/admissions/leads` | Create new lead | Public/Staff |
| GET | `/api/v1/admissions/leads/{id}` | Get lead details | Staff |
| PUT | `/api/v1/admissions/leads/{id}` | Update lead | Staff |
| POST | `/api/v1/admissions/leads/{id}/convert` | Convert lead to application | Staff |
| **Applications** |
| GET | `/api/v1/admissions/applications` | List applications | Staff |
| POST | `/api/v1/admissions/applications` | Create application | Parent/Staff |
| GET | `/api/v1/admissions/applications/{id}` | Get application details | Parent/Staff |
| PUT | `/api/v1/admissions/applications/{id}` | Update application | Parent/Staff |
| POST | `/api/v1/admissions/applications/{id}/submit` | Submit for review | Parent |
| POST | `/api/v1/admissions/applications/{id}/transition` | Move to next stage | Staff |
| **Documents** |
| POST | `/api/v1/admissions/applications/{id}/documents` | Upload document | Parent |
| GET | `/api/v1/admissions/applications/{id}/documents` | List documents | Parent/Staff |
| PUT | `/api/v1/admissions/documents/{id}/verify` | Verify document | Staff |
| **Tests** |
| GET | `/api/v1/admissions/tests` | List scheduled tests | Staff |
| POST | `/api/v1/admissions/tests` | Schedule new test | Admin |
| POST | `/api/v1/admissions/tests/{id}/candidates` | Add candidates | Admin |
| POST | `/api/v1/admissions/tests/{id}/results` | Record results | Staff |
| **Interviews** |
| GET | `/api/v1/admissions/interviews` | List interviews | Staff |
| POST | `/api/v1/admissions/interviews` | Schedule interview | Staff |
| PUT | `/api/v1/admissions/interviews/{id}` | Update interview | Staff |
| POST | `/api/v1/admissions/interviews/{id}/complete` | Complete with notes | Staff |
| **Waitlist** |
| GET | `/api/v1/admissions/waitlist` | View waitlist | Staff |
| POST | `/api/v1/admissions/waitlist/{id}/offer` | Send offer | Admin |
| POST | `/api/v1/admissions/waitlist/{id}/respond` | Accept/Decline offer | Parent |
| **Decision** |
| POST | `/api/v1/admissions/applications/{id}/decide` | Accept/Reject/Waitlist | Admin |
| POST | `/api/v1/admissions/applications/{id}/enroll` | Enroll accepted student | Admin |

### 2.3 Module Structure

```
app/Modules/Admissions/
├── Config/
│   ├── Routes.php
│   └── Services.php
├── Controllers/
│   ├── Api/
│   │   ├── LeadController.php
│   │   ├── ApplicationController.php
│   │   ├── DocumentController.php
│   │   ├── TestController.php
│   │   ├── InterviewController.php
│   │   └── WaitlistController.php
│   └── Web/
│       └── AdmissionsDashboardController.php
├── Models/
│   ├── LeadModel.php
│   ├── ApplicationModel.php
│   ├── ApplicationStageModel.php
│   ├── ApplicationDocumentModel.php
│   ├── EntranceTestModel.php
│   ├── EntranceTestResultModel.php
│   ├── InterviewModel.php
│   └── WaitlistModel.php
├── Services/
│   ├── LeadService.php
│   ├── ApplicationService.php
│   ├── WorkflowService.php
│   ├── DocumentVerificationService.php
│   ├── TestSchedulingService.php
│   ├── InterviewSchedulingService.php
│   ├── WaitlistService.php
│   ├── DecisionService.php
│   ├── EnrollmentService.php
│   └── AdmissionLetterService.php
├── Events/
│   ├── ApplicationSubmitted.php
│   ├── StageTransitioned.php
│   ├── DecisionMade.php
│   └── StudentEnrolled.php
├── Database/
│   └── Migrations/
│       └── 2025-11-27-000001_CreateAdmissionsTables.php
├── Views/
│   ├── dashboard/
│   ├── applications/
│   ├── leads/
│   ├── tests/
│   └── interviews/
└── Tests/
    ├── Unit/
    │   └── WorkflowServiceTest.php
    └── Feature/
        └── AdmissionsApiTest.php
```

### 2.4 Integration Points

- **Learning Module**: Creates student record on enrollment, retrieves class/grade information.
- **HR Module**: Retrieves staff list for interview panels and reviewers.
- **Finance Module**: Generates admission fee invoice, links to payment confirmation.
- **Threads Module**: Sends notifications at each stage (submission, test scheduled, decision made).
- **Reports Module**: Admissions analytics tabs and standalone reports.
- **Foundation Module**: Uses audit service for tracking all changes.

---

## Part 3: Architectural Safeguards
*Target Audience: Architects, Security Engineers*

### 3.1 Tenant Isolation
- All queries MUST be scoped by `school_id`.
- Application numbers are unique per school, not globally.
- Documents are stored in tenant-specific directories.

### 3.2 Document Security
- Uploaded documents stored outside webroot.
- Access via signed URLs with expiration.
- File type validation (whitelist: PDF, JPG, PNG, DOCX).
- Maximum file size enforced (5MB per document).

### 3.3 Workflow Integrity
- Stage transitions must follow defined sequence.
- Only authorized roles can make decisions.
- All state changes are audit-logged.
- Prevent double enrollment.

### 3.4 Waitlist Fairness
- Priority is auto-calculated based on timestamp and score.
- Offer expiration handled by scheduled job.
- Auto-promote next in line when spot opens.

### 3.5 PII Protection
- Parent/student personal data encrypted at rest.
- Access logged for compliance.
- GDPR: Support data export and deletion requests.

---

## Part 4: Embedded Reports for Entity Views
*Target Audience: Frontend Developers, Product Owners*

### 4.1 Application View - Status Tab
| Field | Description |
|:------|:------------|
| Current Stage | Stage name and progress indicator |
| Time in Stage | Days since last transition |
| Documents | List with verification status |
| Test Score | If applicable |
| Interview Status | Scheduled/Completed with date |
| Decision | Accept/Reject/Waitlist with notes |

### 4.2 Admissions Dashboard - Summary Widgets
| Widget | Description |
|:-------|:------------|
| Applications Funnel | Visual funnel by stage |
| Conversion Rate | Lead → Application → Enrolled |
| Pending Reviews | Count requiring action |
| Upcoming Tests | Next 7 days |
| Scheduled Interviews | Today and tomorrow |

---

## Part 5: Test Data Strategy
*Target Audience: QA, Developers*

### 5.1 Seeding Strategy
Use `Modules\Admissions\Database\Seeds\AdmissionsSeeder` to populate:

#### Leads
- 50 leads from various sources (website, referral, open day).
- Mix of statuses: new (20), contacted (15), interested (10), applied (5).

#### Applications
- 30 applications across different stages.
- 10 submitted, 8 under review, 5 pending test, 4 pending interview.
- 3 accepted, waitlisted, rejected.

#### Tests
- 2 scheduled entrance tests for current month.
- 1 completed test with scores.

#### Interviews
- 10 scheduled interviews across next 2 weeks.
- 5 completed interviews with recommendations.

### 5.2 Testing Scenarios
| Scenario | Expected Outcome |
|:---------|:-----------------|
| Submit application with all documents | Status → Submitted, notification sent |
| Upload invalid document type | Validation error returned |
| Schedule test with overlapping time | Conflict detected |
| Accept applicant beyond capacity | Warning shown |
| Auto-promote from waitlist | Offer sent to next in line |
| Enroll accepted student | Student record created in Learning |

---

## Part 6: Development Checklist

- [ ] **Design**: Review and approve this specification.
- [ ] **Tests**: Write failing feature tests (TDD) for core workflows.
- [ ] **Scaffold**: Generate Controllers, Models, and Migrations.
- [ ] **Database**: Run migrations and verify schema.
- [ ] **API**: Implement endpoints with validation and authorization.
- [ ] **Web**: Build application portal views.
- [ ] **Documents**: Implement secure upload/download.
- [ ] **Workflow**: Implement stage transitions with notifications.
- [ ] **Waitlist**: Implement auto-promotion logic.
- [ ] **Enrollment**: Connect to Learning module for student creation.
- [ ] **Review**: Code review and merge to main branch.
