# 👥 HR & Payroll Module Specification

**Version**: 1.0.0
**Status**: Implemented (Documentation)
**Last Updated**: 2025-11-27

---

## Part 1: Feature Definition (The "What" & "Why")
*Target Audience: Product Owners, Stakeholders, Developers*

### 1.1 Overview
The HR module is the "People Operations Hub" of ShuleLabs. It manages the complete employee lifecycle from recruitment to retirement, including departments, designations, attendance tracking, leave management, payroll processing, performance reviews, and document management. This module ensures schools can efficiently manage their workforce while maintaining compliance.

### 1.2 User Stories

- **As an HR Manager**, I want to manage employee records including personal details and contracts, so that I have a complete staff database.
- **As an Admin**, I want to define departments and designations, so that the organizational structure is clear.
- **As a Department Head**, I want to approve leave requests from my team, so that absences are managed properly.
- **As a Finance Officer**, I want to process monthly payroll with automatic deductions, so that staff are paid correctly.
- **As a Staff Member**, I want to view my payslip online, so that I can verify my salary.
- **As a Principal**, I want to conduct performance reviews for teachers, so that I can assess their effectiveness.
- **As an HR Assistant**, I want to manage employee documents securely, so that records are properly maintained.

### 1.3 User Workflows

1. **Employee Onboarding**:
   - HR creates new employee record.
   - HR assigns department, designation, and reporting manager.
   - HR uploads required documents (ID, certificates, contracts).
   - HR sets up salary structure with components.
   - Employee receives login credentials and onboarding checklist.

2. **Staff Attendance**:
   - Staff marks attendance via biometric, mobile app, or web.
   - System records check-in and check-out times.
   - Late arrivals and early departures flagged.
   - Reports show attendance patterns.
   - Attendance feeds into payroll calculations.

3. **Leave Management**:
   - Employee submits leave request with dates and reason.
   - System checks leave balance.
   - Request routed to approver (manager/principal).
   - Approver reviews and approves/rejects.
   - Leave balance updated.
   - Notifications sent to employee.

4. **Payroll Processing**:
   - HR initiates monthly payroll run.
   - System fetches active employees and salary structures.
   - System calculates earnings (basic, allowances).
   - System applies deductions (tax, pension, loans).
   - HR reviews and approves payroll.
   - Payslips generated and distributed.
   - Bank file generated for bulk payment.

5. **Performance Review**:
   - HR sets up review cycle (annual, quarterly).
   - Reviewer (principal/HOD) initiates review.
   - Self-assessment by employee (optional).
   - Reviewer rates against criteria.
   - Meeting scheduled and conducted.
   - Final review saved and acknowledged.

### 1.4 Acceptance Criteria

- [ ] Employee records include all personal and professional details.
- [ ] Departments and designations form a proper hierarchy.
- [ ] Attendance tracked with multiple input methods.
- [ ] Leave types configurable with accrual rules.
- [ ] Leave approval workflow with notifications.
- [ ] Payroll calculates correctly with all components.
- [ ] Tax calculations based on configured tax tables.
- [ ] Payslips generated and accessible to staff.
- [ ] Performance reviews with customizable criteria.
- [ ] Documents stored securely with access control.
- [ ] All data scoped by school_id for multi-tenancy.

---

## Part 2: Technical Specification (The "How")
*Target Audience: Developers, Architects*

### 2.1 Database Schema

#### `departments`
Organizational departments.
```sql
CREATE TABLE departments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20),
    parent_id INT,
    head_id INT,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (head_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uk_school_code (school_id, code)
);
```

#### `designations`
Job titles and levels.
```sql
CREATE TABLE designations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20),
    level INT,
    min_salary DECIMAL(12,2),
    max_salary DECIMAL(12,2),
    is_teaching BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    UNIQUE KEY uk_school_code (school_id, code)
);
```

#### `employees`
Extended employee information linked to users.
```sql
CREATE TABLE employees (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    school_id INT NOT NULL,
    employee_number VARCHAR(50) NOT NULL,
    department_id INT,
    designation_id INT,
    reports_to INT,
    employment_type ENUM('permanent', 'contract', 'temporary', 'intern') DEFAULT 'permanent',
    employment_status ENUM('active', 'on_leave', 'suspended', 'resigned', 'terminated', 'retired') DEFAULT 'active',
    join_date DATE NOT NULL,
    confirmation_date DATE,
    termination_date DATE,
    termination_reason TEXT,
    probation_end_date DATE,
    contract_end_date DATE,
    work_location VARCHAR(100),
    shift VARCHAR(50),
    
    -- Emergency Contact
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(20),
    emergency_contact_relation VARCHAR(50),
    
    -- Bank Details
    bank_name VARCHAR(100),
    bank_branch VARCHAR(100),
    bank_account_number VARCHAR(50),
    bank_account_name VARCHAR(100),
    
    -- Tax Information
    tax_pin VARCHAR(50),
    nssf_number VARCHAR(50),
    nhif_number VARCHAR(50),
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (designation_id) REFERENCES designations(id) ON DELETE SET NULL,
    FOREIGN KEY (reports_to) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uk_school_empno (school_id, employee_number),
    INDEX idx_department (department_id),
    INDEX idx_status (employment_status)
);
```

#### `staff_attendance`
Daily staff attendance records.
```sql
CREATE TABLE staff_attendance (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    check_in_time DATETIME,
    check_out_time DATETIME,
    status ENUM('present', 'absent', 'late', 'half_day', 'on_leave', 'holiday', 'weekend') DEFAULT 'present',
    check_in_source ENUM('biometric', 'web', 'mobile', 'manual') DEFAULT 'manual',
    check_out_source ENUM('biometric', 'web', 'mobile', 'manual'),
    work_hours DECIMAL(4,2),
    overtime_hours DECIMAL(4,2) DEFAULT 0,
    notes TEXT,
    approved_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uk_employee_date (employee_id, attendance_date),
    INDEX idx_date (attendance_date)
);
```

#### `leave_types`
Types of leave available.
```sql
CREATE TABLE leave_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20),
    days_per_year DECIMAL(4,1),
    is_paid BOOLEAN DEFAULT TRUE,
    carry_forward_allowed BOOLEAN DEFAULT FALSE,
    max_carry_forward DECIMAL(4,1),
    requires_approval BOOLEAN DEFAULT TRUE,
    min_notice_days INT DEFAULT 0,
    max_consecutive_days INT,
    applicable_to ENUM('all', 'teaching', 'non_teaching') DEFAULT 'all',
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    UNIQUE KEY uk_school_code (school_id, code)
);
```

#### `leave_balances`
Employee leave balances per type.
```sql
CREATE TABLE leave_balances (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    leave_type_id INT NOT NULL,
    year INT NOT NULL,
    opening_balance DECIMAL(4,1) DEFAULT 0,
    accrued DECIMAL(4,1) DEFAULT 0,
    used DECIMAL(4,1) DEFAULT 0,
    adjustment DECIMAL(4,1) DEFAULT 0,
    carried_forward DECIMAL(4,1) DEFAULT 0,
    closing_balance DECIMAL(4,1) AS (opening_balance + accrued + carried_forward + adjustment - used) STORED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (leave_type_id) REFERENCES leave_types(id) ON DELETE CASCADE,
    UNIQUE KEY uk_employee_type_year (employee_id, leave_type_id, year)
);
```

#### `leave_requests`
Leave applications.
```sql
CREATE TABLE leave_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    leave_type_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    days_requested DECIMAL(4,1) NOT NULL,
    reason TEXT,
    contact_during_leave VARCHAR(100),
    handover_to INT,
    status ENUM('draft', 'pending', 'approved', 'rejected', 'cancelled') DEFAULT 'draft',
    approved_by INT,
    approved_at DATETIME,
    rejection_reason TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (leave_type_id) REFERENCES leave_types(id) ON DELETE CASCADE,
    FOREIGN KEY (handover_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_employee_status (employee_id, status),
    INDEX idx_dates (start_date, end_date)
);
```

#### `salary_structures`
Employee salary components.
```sql
CREATE TABLE salary_structures (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    effective_from DATE NOT NULL,
    effective_to DATE,
    basic_salary DECIMAL(12,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'KES',
    payment_frequency ENUM('monthly', 'bi_weekly', 'weekly') DEFAULT 'monthly',
    is_current BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_employee_current (employee_id, is_current)
);
```

#### `salary_components`
Allowances and deductions.
```sql
CREATE TABLE salary_components (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20),
    component_type ENUM('earning', 'deduction') NOT NULL,
    calculation_type ENUM('fixed', 'percentage', 'formula') DEFAULT 'fixed',
    default_value DECIMAL(12,2),
    percentage_of VARCHAR(50),
    formula TEXT,
    is_taxable BOOLEAN DEFAULT TRUE,
    is_recurring BOOLEAN DEFAULT TRUE,
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    UNIQUE KEY uk_school_code (school_id, code)
);
```

#### `salary_component_assignments`
Components assigned to salary structures.
```sql
CREATE TABLE salary_component_assignments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    salary_structure_id INT NOT NULL,
    component_id INT NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (salary_structure_id) REFERENCES salary_structures(id) ON DELETE CASCADE,
    FOREIGN KEY (component_id) REFERENCES salary_components(id) ON DELETE CASCADE,
    UNIQUE KEY uk_structure_component (salary_structure_id, component_id)
);
```

#### `payroll_runs`
Monthly payroll processing.
```sql
CREATE TABLE payroll_runs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    period_year INT NOT NULL,
    period_month INT NOT NULL,
    name VARCHAR(100),
    status ENUM('draft', 'processing', 'approved', 'paid', 'cancelled') DEFAULT 'draft',
    total_gross DECIMAL(15,2) DEFAULT 0,
    total_deductions DECIMAL(15,2) DEFAULT 0,
    total_net DECIMAL(15,2) DEFAULT 0,
    employee_count INT DEFAULT 0,
    payment_date DATE,
    bank_file_path VARCHAR(500),
    processed_by INT,
    processed_at DATETIME,
    approved_by INT,
    approved_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uk_school_period (school_id, period_year, period_month),
    INDEX idx_status (status)
);
```

#### `payroll_entries`
Individual employee payroll line items.
```sql
CREATE TABLE payroll_entries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    payroll_run_id INT NOT NULL,
    employee_id INT NOT NULL,
    basic_salary DECIMAL(12,2) NOT NULL,
    gross_earnings DECIMAL(12,2) NOT NULL,
    total_deductions DECIMAL(12,2) NOT NULL,
    net_salary DECIMAL(12,2) NOT NULL,
    tax_amount DECIMAL(12,2) DEFAULT 0,
    nssf_amount DECIMAL(12,2) DEFAULT 0,
    nhif_amount DECIMAL(12,2) DEFAULT 0,
    loan_deduction DECIMAL(12,2) DEFAULT 0,
    other_deductions DECIMAL(12,2) DEFAULT 0,
    days_worked INT,
    days_absent INT DEFAULT 0,
    overtime_hours DECIMAL(5,2) DEFAULT 0,
    overtime_amount DECIMAL(12,2) DEFAULT 0,
    components_breakdown JSON,
    bank_account_number VARCHAR(50),
    bank_name VARCHAR(100),
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    payment_reference VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (payroll_run_id) REFERENCES payroll_runs(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    UNIQUE KEY uk_run_employee (payroll_run_id, employee_id),
    INDEX idx_employee (employee_id)
);
```

#### `performance_reviews`
Staff performance evaluations.
```sql
CREATE TABLE performance_reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    review_period_start DATE NOT NULL,
    review_period_end DATE NOT NULL,
    review_type ENUM('annual', 'quarterly', 'probation', 'ad_hoc') DEFAULT 'annual',
    status ENUM('draft', 'self_assessment', 'review_in_progress', 'completed', 'acknowledged') DEFAULT 'draft',
    overall_rating DECIMAL(3,2),
    strengths TEXT,
    areas_for_improvement TEXT,
    goals_for_next_period TEXT,
    reviewer_comments TEXT,
    employee_comments TEXT,
    acknowledgement_date DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_employee (employee_id),
    INDEX idx_period (review_period_start, review_period_end)
);
```

#### `employee_documents`
Staff document storage.
```sql
CREATE TABLE employee_documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    document_type VARCHAR(100) NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT,
    mime_type VARCHAR(100),
    expiry_date DATE,
    is_verified BOOLEAN DEFAULT FALSE,
    verified_by INT,
    verified_at DATETIME,
    notes TEXT,
    uploaded_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    INDEX idx_employee_type (employee_id, document_type)
);
```

### 2.2 API Endpoints (Mobile-First)

| Method | Endpoint | Description | Access |
|:-------|:---------|:------------|:-------|
| **Employees** |
| GET | `/api/v1/hr/employees` | List employees | HR/Admin |
| POST | `/api/v1/hr/employees` | Create employee | HR/Admin |
| GET | `/api/v1/hr/employees/{id}` | Get employee details | HR/Self |
| PUT | `/api/v1/hr/employees/{id}` | Update employee | HR/Admin |
| GET | `/api/v1/hr/employees/me` | Get current employee profile | Staff |
| **Departments** |
| GET | `/api/v1/hr/departments` | List departments | All |
| POST | `/api/v1/hr/departments` | Create department | Admin |
| **Attendance** |
| POST | `/api/v1/hr/attendance/check-in` | Check in | Staff |
| POST | `/api/v1/hr/attendance/check-out` | Check out | Staff |
| GET | `/api/v1/hr/attendance/my` | Get own attendance | Staff |
| GET | `/api/v1/hr/attendance/department/{id}` | Get department attendance | Manager |
| **Leave** |
| GET | `/api/v1/hr/leave/types` | List leave types | Staff |
| GET | `/api/v1/hr/leave/balance` | Get leave balance | Staff |
| POST | `/api/v1/hr/leave/request` | Submit leave request | Staff |
| GET | `/api/v1/hr/leave/requests` | List leave requests | HR/Manager |
| POST | `/api/v1/hr/leave/requests/{id}/approve` | Approve request | Manager |
| POST | `/api/v1/hr/leave/requests/{id}/reject` | Reject request | Manager |
| **Payroll** |
| GET | `/api/v1/hr/payroll/runs` | List payroll runs | HR/Admin |
| POST | `/api/v1/hr/payroll/runs` | Create payroll run | HR |
| POST | `/api/v1/hr/payroll/runs/{id}/process` | Process payroll | HR |
| POST | `/api/v1/hr/payroll/runs/{id}/approve` | Approve payroll | Admin |
| GET | `/api/v1/hr/payroll/payslips/my` | Get own payslips | Staff |
| GET | `/api/v1/hr/payroll/payslips/{id}` | Get payslip PDF | Staff/HR |
| **Performance** |
| GET | `/api/v1/hr/performance/reviews` | List reviews | HR/Manager |
| POST | `/api/v1/hr/performance/reviews` | Create review | Manager |
| POST | `/api/v1/hr/performance/reviews/{id}/complete` | Complete review | Manager |

### 2.3 Module Structure

```
app/Modules/HR/
├── Config/
│   ├── Routes.php
│   └── Services.php
├── Controllers/
│   ├── Api/
│   │   ├── EmployeeController.php
│   │   ├── DepartmentController.php
│   │   ├── AttendanceController.php
│   │   ├── LeaveController.php
│   │   ├── PayrollController.php
│   │   ├── PerformanceController.php
│   │   └── DocumentController.php
│   └── Web/
│       └── HRDashboardController.php
├── Models/
│   ├── DepartmentModel.php
│   ├── DesignationModel.php
│   ├── EmployeeModel.php
│   ├── StaffAttendanceModel.php
│   ├── LeaveTypeModel.php
│   ├── LeaveBalanceModel.php
│   ├── LeaveRequestModel.php
│   ├── SalaryStructureModel.php
│   ├── SalaryComponentModel.php
│   ├── PayrollRunModel.php
│   ├── PayrollEntryModel.php
│   ├── PerformanceReviewModel.php
│   └── EmployeeDocumentModel.php
├── Services/
│   ├── EmployeeService.php
│   ├── AttendanceService.php
│   ├── LeaveService.php
│   ├── LeaveAccrualService.php
│   ├── PayrollService.php
│   ├── TaxCalculatorService.php
│   ├── PayslipGeneratorService.php
│   ├── PerformanceService.php
│   └── DocumentService.php
├── Libraries/
│   ├── KenyaTaxCalculator.php
│   ├── NSSFCalculator.php
│   ├── NHIFCalculator.php
│   └── BankFileGenerator.php
├── Database/
│   └── Migrations/
│       └── 2025-11-27-000001_CreateHRTables.php
├── Views/
│   ├── dashboard/
│   ├── employees/
│   ├── attendance/
│   ├── leave/
│   ├── payroll/
│   └── performance/
└── Tests/
    ├── Unit/
    │   └── PayrollServiceTest.php
    └── Feature/
        └── HRApiTest.php
```

### 2.4 Integration Points

- **Learning Module**: Teacher information for class allocations.
- **Finance Module**: Payroll journal entries to general ledger.
- **Threads Module**: Leave approval notifications, payslip alerts.
- **Reports Module**: HR analytics in staff view tabs.
- **Foundation Module**: Audit logging for sensitive data changes.
- **Scheduler Module**: Monthly payroll reminders, leave accrual jobs.

---

## Part 3: Architectural Safeguards
*Target Audience: Architects, Security Engineers*

### 3.1 Sensitive Data Protection
- Bank account numbers partially masked in displays.
- Tax PINs encrypted at rest.
- Access to payroll data restricted by role.
- Audit trail for all salary changes.

### 3.2 Leave Calculation Accuracy
- Use transactional updates for leave balance changes.
- Validate sufficient balance before approval.
- Handle partial days correctly.
- Consider weekends and holidays in day calculation.

### 3.3 Payroll Integrity
- Double-check all calculations before approval.
- Lock payroll run after approval.
- Generate checksum for bank files.
- Maintain complete audit trail.

### 3.4 Statutory Compliance (Kenya)
- PAYE tax calculated per current tax bands.
- NSSF contribution at statutory rates.
- NHIF based on salary scale.
- Generate P9 forms for tax returns.

### 3.5 Performance Review Confidentiality
- Reviews visible only to employee and reviewer.
- HR admin has oversight access.
- Acknowledgement required before closure.

---

## Part 4: Embedded Reports for Entity Views
*Target Audience: Frontend Developers, Product Owners*

### 4.1 Staff View - HR Tab
| Field | Description |
|:------|:------------|
| Employee Details | Basic info, department, designation |
| Leave Balance | Current balance by type |
| Attendance Summary | Present/Absent this month |
| Recent Payslips | Last 3 months |
| Performance Rating | Latest review score |

### 4.2 Department View - Staff Tab
| Field | Description |
|:------|:------------|
| Employee Count | Active staff in department |
| Attendance Today | Present/Absent count |
| Pending Leave | Requests awaiting approval |
| Payroll Summary | Department salary total |

---

## Part 5: Test Data Strategy
*Target Audience: QA, Developers*

### 5.1 Seeding Strategy
Use `Modules\HR\Database\Seeds\HRSeeder`:

#### Structure
- 5 departments (Administration, Academic, Finance, Support, Security).
- 10 designations with salary ranges.
- Leave types: Annual (21), Sick (14), Maternity (90), Study (10).

#### Employees
- 50 employees across departments.
- Mix of employment types.
- Salary structures with components.
- Leave balances initialized.

#### Historical Data
- 6 months of attendance records.
- Leave requests with approvals/rejections.
- 3 payroll runs (completed).
- Performance reviews for annual cycle.

### 5.2 Testing Scenarios
| Scenario | Expected Outcome |
|:---------|:-----------------|
| Request leave exceeding balance | Validation error |
| Process payroll with missing data | Warning with skip option |
| Calculate tax for high earner | Correct PAYE amount |
| Overlapping leave request | Conflict detected |
| View another's payslip | Access denied |

---

## Part 6: Development Checklist

- [x] **Database**: Core tables created and migrated.
- [x] **Employees**: CRUD implemented.
- [x] **Departments**: Department management working.
- [x] **Attendance**: Check-in/Check-out working.
- [ ] **Attendance**: Biometric integration.
- [x] **Leave**: Leave request and approval.
- [ ] **Leave**: Accrual automation.
- [x] **Payroll**: Basic payroll run.
- [ ] **Payroll**: Tax calculator for Kenya.
- [ ] **Payroll**: Bank file generation.
- [x] **Documents**: Document upload working.
- [ ] **Performance**: Review cycle implementation.
- [ ] **Integration**: Connect with Finance for GL entries.
