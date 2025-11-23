# 🚀 Super Developer: Complete Multi-School Implementation & 90%+ Coverage

**Objective**: Build a production-ready multi-tenant school management system with comprehensive testing across all user types and schools.

---

## Mission Statement

You are an elite full-stack developer tasked with completing the ShuleLabs CI4 multi-school platform. Your goal is to:

1. **Implement complete multi-school/multi-tenant architecture**
2. **Achieve >90% test coverage across ALL 8 modules**
3. **Test every user type across multiple schools**
4. **Fix all bugs and optimize performance**
5. **Deploy a production-ready system**

---

## Phase 1: Multi-School Architecture Implementation (45 minutes)

### 1.1 School/Tenant Infrastructure

**Create the following:**

#### Database Schema
```sql
-- Schools/Tenants Table
CREATE TABLE schools (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_code VARCHAR(20) UNIQUE NOT NULL,
    school_name VARCHAR(255) NOT NULL,
    school_type ENUM('primary', 'secondary', 'mixed', 'college') DEFAULT 'mixed',
    country VARCHAR(100) DEFAULT 'Kenya',
    county VARCHAR(100),
    sub_county VARCHAR(100),
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    website VARCHAR(255),
    logo_url VARCHAR(255),
    timezone VARCHAR(50) DEFAULT 'Africa/Nairobi',
    currency VARCHAR(10) DEFAULT 'KES',
    academic_year_start DATE,
    academic_year_end DATE,
    subscription_plan ENUM('free', 'basic', 'premium', 'enterprise') DEFAULT 'free',
    subscription_expires_at DATETIME,
    max_students INT DEFAULT 100,
    max_teachers INT DEFAULT 10,
    is_active BOOLEAN DEFAULT TRUE,
    settings JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- School Users Junction (Multi-tenant access)
CREATE TABLE school_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    is_primary_school BOOLEAN DEFAULT FALSE,
    joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES ci4_users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES ci4_roles(id),
    UNIQUE KEY unique_school_user (school_id, user_id)
);

-- School Classes
CREATE TABLE school_classes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    class_name VARCHAR(100) NOT NULL,
    grade_level VARCHAR(20),
    section VARCHAR(10),
    class_teacher_id INT,
    academic_year VARCHAR(20),
    max_capacity INT DEFAULT 40,
    room_number VARCHAR(20),
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (class_teacher_id) REFERENCES ci4_users(id) ON DELETE SET NULL
);

-- Student Enrollments
CREATE TABLE student_enrollments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    school_id INT NOT NULL,
    class_id INT,
    enrollment_date DATE NOT NULL,
    status ENUM('active', 'suspended', 'graduated', 'transferred', 'withdrawn') DEFAULT 'active',
    admission_number VARCHAR(50),
    parent_id INT,
    FOREIGN KEY (student_id) REFERENCES ci4_users(id) ON DELETE CASCADE,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES school_classes(id) ON DELETE SET NULL,
    FOREIGN KEY (parent_id) REFERENCES ci4_users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_admission (school_id, admission_number)
);
```

#### Migrations to Create
1. `CreateSchoolsTable.php` - Schools/tenants
2. `CreateSchoolUsersTable.php` - School-user relationships
3. `CreateSchoolClassesTable.php` - Classes per school
4. `CreateStudentEnrollmentsTable.php` - Student enrollments

### 1.2 Tenant Context Service

**Create**: `app/Services/TenantService.php`

```php
<?php

namespace App\Services;

use App\Models\SchoolModel;
use CodeIgniter\HTTP\RequestInterface;

class TenantService
{
    protected ?int $currentSchoolId = null;
    protected ?array $currentSchool = null;
    protected RequestInterface $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Set current school context from session or subdomain
     */
    public function setCurrentSchool(): void
    {
        $session = session();
        
        // Priority 1: Session (user selected school)
        if ($session->has('current_school_id')) {
            $this->currentSchoolId = $session->get('current_school_id');
            $this->loadSchool($this->currentSchoolId);
            return;
        }

        // Priority 2: Subdomain routing (e.g., nairobi-primary.shulelabs.com)
        $host = $this->request->getServer('HTTP_HOST');
        if (preg_match('/^([a-z0-9-]+)\.shulelabs\./', $host, $matches)) {
            $schoolCode = $matches[1];
            $this->loadSchoolByCode($schoolCode);
            return;
        }

        // Priority 3: User's primary school
        if ($session->has('user_id')) {
            $this->loadUserPrimarySchool($session->get('user_id'));
        }
    }

    /**
     * Get current school ID
     */
    public function getCurrentSchoolId(): ?int
    {
        return $this->currentSchoolId;
    }

    /**
     * Get current school data
     */
    public function getCurrentSchool(): ?array
    {
        return $this->currentSchool;
    }

    /**
     * Switch user to different school
     */
    public function switchSchool(int $schoolId, int $userId): bool
    {
        // Verify user has access to this school
        $schoolUserModel = model('SchoolUserModel');
        $access = $schoolUserModel->where([
            'school_id' => $schoolId,
            'user_id' => $userId
        ])->first();

        if (!$access) {
            return false;
        }

        session()->set('current_school_id', $schoolId);
        $this->loadSchool($schoolId);
        return true;
    }

    /**
     * Get all schools for a user
     */
    public function getUserSchools(int $userId): array
    {
        $schoolUserModel = model('SchoolUserModel');
        return $schoolUserModel->getUserSchools($userId);
    }

    // Private helper methods
    private function loadSchool(int $schoolId): void
    {
        $schoolModel = new SchoolModel();
        $school = $schoolModel->find($schoolId);
        
        if ($school && $school['is_active']) {
            $this->currentSchool = $school;
            $this->currentSchoolId = $schoolId;
        }
    }

    private function loadSchoolByCode(string $code): void
    {
        $schoolModel = new SchoolModel();
        $school = $schoolModel->where('school_code', $code)->first();
        
        if ($school && $school['is_active']) {
            $this->currentSchool = $school;
            $this->currentSchoolId = $school['id'];
            session()->set('current_school_id', $school['id']);
        }
    }

    private function loadUserPrimarySchool(int $userId): void
    {
        $schoolUserModel = model('SchoolUserModel');
        $primary = $schoolUserModel->where([
            'user_id' => $userId,
            'is_primary_school' => true
        ])->first();

        if ($primary) {
            $this->loadSchool($primary['school_id']);
        }
    }
}
```

### 1.3 Tenant-Aware Base Model

**Create**: `app/Models/TenantModel.php`

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

abstract class TenantModel extends Model
{
    protected bool $useTenant = true;
    protected string $tenantColumn = 'school_id';

    /**
     * Automatically scope queries to current tenant
     */
    protected function scopeToTenant(): void
    {
        if ($this->useTenant && $this->hasColumn($this->tenantColumn)) {
            $tenantService = service('tenant');
            $schoolId = $tenantService->getCurrentSchoolId();
            
            if ($schoolId) {
                $this->where($this->tenantColumn, $schoolId);
            }
        }
    }

    /**
     * Override find to include tenant scope
     */
    public function find($id = null)
    {
        $this->scopeToTenant();
        return parent::find($id);
    }

    /**
     * Override findAll to include tenant scope
     */
    public function findAll(int $limit = 0, int $offset = 0)
    {
        $this->scopeToTenant();
        return parent::findAll($limit, $offset);
    }

    /**
     * Check if table has a specific column
     */
    protected function hasColumn(string $column): bool
    {
        $fields = $this->db->getFieldNames($this->table);
        return in_array($column, $fields);
    }

    /**
     * Disable tenant scoping for specific query
     */
    public function withoutTenant(): self
    {
        $this->useTenant = false;
        return $this;
    }

    /**
     * Global scope for all tenant queries
     */
    public function forSchool(int $schoolId): self
    {
        $this->where($this->tenantColumn, $schoolId);
        return $this;
    }
}
```

### 1.4 Tenant Filter

**Create**: `app/Filters/TenantFilter.php`

```php
<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class TenantFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $tenantService = service('tenant');
        $tenantService->setCurrentSchool();

        // Verify user has access to current school
        $session = session();
        if ($session->has('user_id') && $tenantService->getCurrentSchoolId()) {
            $schools = $tenantService->getUserSchools($session->get('user_id'));
            $schoolIds = array_column($schools, 'school_id');
            
            if (!in_array($tenantService->getCurrentSchoolId(), $schoolIds)) {
                return redirect()->to('/dashboard/select-school')
                    ->with('error', 'You do not have access to this school');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed
    }
}
```

---

## Phase 2: Comprehensive Test Data Generation (30 minutes)

### 2.1 Multi-School Seeder

**Create**: `app/Database/Seeds/MultiSchoolSeeder.php`

```php
<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MultiSchoolSeeder extends Seeder
{
    public function run()
    {
        // Create 5 diverse schools
        $schools = [
            [
                'school_code' => 'NPS001',
                'school_name' => 'Nairobi Primary School',
                'school_type' => 'primary',
                'county' => 'Nairobi',
                'sub_county' => 'Westlands',
                'phone' => '+254712345001',
                'email' => 'info@nairobiprimary.ac.ke',
                'max_students' => 500,
                'max_teachers' => 30,
                'subscription_plan' => 'premium'
            ],
            [
                'school_code' => 'MSS001',
                'school_name' => 'Mombasa Secondary School',
                'school_type' => 'secondary',
                'county' => 'Mombasa',
                'sub_county' => 'Mvita',
                'phone' => '+254712345002',
                'email' => 'admin@mombasasecondary.ac.ke',
                'max_students' => 800,
                'max_teachers' => 50,
                'subscription_plan' => 'enterprise'
            ],
            [
                'school_code' => 'KMA001',
                'school_name' => 'Kisumu Mixed Academy',
                'school_type' => 'mixed',
                'county' => 'Kisumu',
                'sub_county' => 'Kisumu Central',
                'phone' => '+254712345003',
                'email' => 'hello@kisumumixed.ac.ke',
                'max_students' => 600,
                'max_teachers' => 40,
                'subscription_plan' => 'basic'
            ],
            [
                'school_code' => 'ETC001',
                'school_name' => 'Eldoret Technical College',
                'school_type' => 'college',
                'county' => 'Uasin Gishu',
                'sub_county' => 'Eldoret East',
                'phone' => '+254712345004',
                'email' => 'registrar@eldorettech.ac.ke',
                'max_students' => 1000,
                'max_teachers' => 60,
                'subscription_plan' => 'enterprise'
            ],
            [
                'school_code' => 'NKS001',
                'school_name' => 'Nakuru Kids School',
                'school_type' => 'primary',
                'county' => 'Nakuru',
                'sub_county' => 'Nakuru East',
                'phone' => '+254712345005',
                'email' => 'contact@nakurukids.ac.ke',
                'max_students' => 300,
                'max_teachers' => 20,
                'subscription_plan' => 'free'
            ]
        ];

        foreach ($schools as $school) {
            $this->db->table('schools')->insert($school);
        }

        echo "✅ Created 5 schools\n";
    }
}
```

### 2.2 Comprehensive User Seeder

**Create**: `app/Database/Seeds/MultiSchoolUserSeeder.php`

**Generate 150+ users across 5 schools:**

- **School 1 (Nairobi Primary)**: 40 users
  - 1 School Admin
  - 8 Teachers
  - 25 Students
  - 5 Parents
  - 1 Librarian

- **School 2 (Mombasa Secondary)**: 50 users
  - 1 School Admin
  - 12 Teachers
  - 30 Students
  - 5 Parents
  - 1 Accountant
  - 1 Librarian

- **School 3 (Kisumu Mixed)**: 35 users
  - 1 School Admin
  - 10 Teachers
  - 20 Students
  - 3 Parents
  - 1 Receptionist

- **School 4 (Eldoret Technical)**: 45 users
  - 2 School Admins
  - 15 Teachers
  - 25 Students
  - 2 Parents
  - 1 Accountant

- **School 5 (Nakuru Kids)**: 25 users
  - 1 School Admin
  - 5 Teachers
  - 15 Students
  - 4 Parents

**Total**: 195 users across 5 schools

### 2.3 Classes and Enrollments Seeder

**Create**: `app/Database/Seeds/ClassEnrollmentSeeder.php`

**Generate:**
- 30+ classes across all schools
- Enroll all students in appropriate classes
- Assign class teachers
- Link parents to students

---

## Phase 3: Module Implementation & >90% Coverage (2 hours)

### Target: Every module achieves >90% test coverage

### 3.1 Foundation Module (Target: 95% coverage)

**Implement:**

#### Controllers
- `SchoolController.php` - CRUD for schools
- `SchoolSwitcherController.php` - Switch between schools
- `TenantDashboardController.php` - School-specific dashboard

#### Services
- `SchoolService.php` - School business logic
- `EnrollmentService.php` - Student enrollment management
- `ClassManagementService.php` - Class operations

#### Tests (30+ tests)
```php
// tests/Foundation/SchoolServiceTest.php
- testCreateSchool()
- testUpdateSchool()
- testDeleteSchool()
- testActivateDeactivateSchool()
- testSchoolSubscriptionManagement()
- testSchoolCapacityLimits()
- testSchoolSettingsManagement()

// tests/Foundation/TenantServiceTest.php
- testSetCurrentSchoolFromSession()
- testSetCurrentSchoolFromSubdomain()
- testSetCurrentSchoolFromUserPrimary()
- testSwitchSchool()
- testGetUserSchools()
- testTenantIsolation()
- testUnauthorizedSchoolAccess()

// tests/Foundation/EnrollmentServiceTest.php
- testEnrollStudent()
- testTransferStudent()
- testWithdrawStudent()
- testGraduateStudent()
- testBulkEnrollment()
- testEnrollmentCapacityCheck()
```

### 3.2 HR Module (Target: 92% coverage)

**Implement:**

#### Features
- Teacher assignment to schools
- Multi-school teacher access (e.g., substitute teachers)
- Role-based permissions per school
- Staff attendance tracking per school

#### Tests (25+ tests)
```php
// tests/Hr/TeacherManagementTest.php
- testAssignTeacherToSchool()
- testAssignTeacherToMultipleSchools()
- testRemoveTeacherFromSchool()
- testTeacherPermissionsPerSchool()
- testClassTeacherAssignment()
- testSubstituteTeacherManagement()

// tests/Hr/StaffAttendanceTest.php
- testRecordStaffAttendance()
- testStaffAttendanceBySchool()
- testAttendanceReports()
- testLateArrivalTracking()
```

### 3.3 Finance Module (Target: 94% coverage)

**Implement:**

#### Features
- School-specific invoices and payments
- Fee structures per school
- M-Pesa integration per school
- Financial reports per school
- Multi-school financial dashboard (for group owners)

#### Tests (28+ tests)
```php
// tests/Finance/InvoiceServiceTest.php
- testCreateInvoiceForSchool()
- testInvoiceIsolationBetweenSchools()
- testBulkInvoiceGeneration()
- testInvoicePaymentTracking()
- testOverdueInvoiceIdentification()

// tests/Finance/PaymentServiceTest.php
- testRecordPayment()
- testMpesaPaymentIntegration()
- testPaymentReconciliation()
- testRefundProcessing()
- testPaymentReports()

// tests/Finance/FeeStructureTest.php
- testCreateFeeStructureForSchool()
- testTermlyFeeManagement()
- testDiscountApplication()
- testFeeWaivers()
```

### 3.4 Learning Module (Target: 93% coverage)

**Implement:**

#### Features
- Curriculum per school
- Assignments and grading per class
- Attendance tracking per school
- Report cards and transcripts
- Parent-teacher communication

#### Tests (32+ tests)
```php
// tests/Learning/CourseManagementTest.php
- testCreateCourse()
- testAssignCourseToClass()
- testCourseEnrollment()
- testCourseCompletionTracking()

// tests/Learning/AssignmentTest.php
- testCreateAssignment()
- testSubmitAssignment()
- testGradeAssignment()
- testAssignmentDeadlines()
- testBulkGrading()

// tests/Learning/AttendanceTest.php
- testRecordStudentAttendance()
- testAttendancePerClass()
- testAttendanceReports()
- testAbsenteeNotifications()
- testAttendancePercentageCalculation()

// tests/Learning/GradingTest.php
- testRecordGrade()
- testCalculateGPA()
- testGenerateReportCard()
- testTranscriptGeneration()
- testGradeVerification()
```

### 3.5 Library Module (Target: 91% coverage)

**Implement:**

#### Features
- Library catalog per school
- Book borrowing and returns
- Multi-school library sharing (optional)
- Overdue book management
- Library reports

#### Tests (20+ tests)
```php
// tests/Library/CatalogTest.php
- testAddBookToLibrary()
- testUpdateBookDetails()
- testSearchCatalog()
- testBookAvailability()

// tests/Library/BorrowingTest.php
- testBorrowBook()
- testReturnBook()
- testRenewBook()
- testOverdueBookDetection()
- testFineCalculation()
- testBorrowingHistory()
- testMaxBorrowingLimits()
```

### 3.6 Inventory Module (Target: 90% coverage)

**Implement:**

#### Features
- School assets and inventory
- Stock management per school
- Procurement and requisitions
- Inventory transfers between schools
- Asset depreciation tracking

#### Tests (22+ tests)
```php
// tests/Inventory/AssetManagementTest.php
- testRegisterAsset()
- testAssetAssignment()
- testAssetTracking()
- testAssetDepreciation()
- testAssetDisposal()

// tests/Inventory/StockManagementTest.php
- testAddStock()
- testUpdateStockLevels()
- testLowStockAlerts()
- testStockTransfer()
- testStockValuation()
- testInventoryReports()
```

### 3.7 Mobile Module (Target: 92% coverage)

**Implement:**

#### API Endpoints (All tenant-aware)
- `POST /api/v1/auth/login` - Login with school selection
- `GET /api/v1/schools` - User's accessible schools
- `POST /api/v1/schools/switch` - Switch current school
- `GET /api/v1/dashboard` - School-specific dashboard
- `GET /api/v1/students` - Students in current school
- `GET /api/v1/classes` - Classes in current school
- `GET /api/v1/attendance` - Attendance data
- `POST /api/v1/assignments` - Create assignment
- `GET /api/v1/grades` - Student grades

#### Tests (24+ tests)
```php
// tests/Mobile/AuthApiTest.php
- testLoginWithSchoolSelection()
- testLoginReturnsUserSchools()
- testTokenValidation()
- testSchoolSwitching()

// tests/Mobile/StudentApiTest.php
- testGetStudentsBySchool()
- testStudentIsolation()
- testStudentSearch()
- testStudentDetails()

// tests/Mobile/AttendanceApiTest.php
- testRecordAttendanceViaApi()
- testGetAttendanceByClass()
- testAttendanceSummary()
```

### 3.8 Threads Module (Target: 91% coverage)

**Implement:**

#### Features
- Messaging within school context
- Announcements per school
- Teacher-parent messaging
- Student-teacher messaging
- Group chats per class

#### Tests (18+ tests)
```php
// tests/Threads/MessagingTest.php
- testSendMessage()
- testReceiveMessage()
- testMessageIsolation()
- testGroupChat()
- testAnnouncementBroadcast()
- testMessageNotifications()
- testMessageSearch()
```

---

## Phase 4: Cross-School Testing (1 hour)

### 4.1 Data Isolation Tests

**Create**: `tests/Integration/TenantIsolationTest.php`

```php
<?php

namespace Tests\Integration;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class TenantIsolationTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    /**
     * Test that School A cannot see School B's students
     */
    public function testStudentIsolation()
    {
        // Create students in two different schools
        $school1Student = $this->createStudent(1); // Nairobi Primary
        $school2Student = $this->createStudent(2); // Mombasa Secondary
        
        // Set tenant context to School 1
        service('tenant')->switchSchool(1, $this->superAdminId);
        
        $studentModel = model('StudentModel');
        $students = $studentModel->findAll();
        
        // Should only see School 1 students
        $this->assertContains($school1Student['id'], array_column($students, 'id'));
        $this->assertNotContains($school2Student['id'], array_column($students, 'id'));
    }

    /**
     * Test invoice isolation between schools
     */
    public function testInvoiceIsolation()
    {
        // Similar pattern for invoices
    }

    /**
     * Test class isolation
     */
    public function testClassIsolation()
    {
        // Similar pattern for classes
    }

    /**
     * Test that shared resources are accessible
     */
    public function testSharedResourceAccess()
    {
        // SuperAdmin should see all schools
        // Multi-school teachers should see data from assigned schools
    }
}
```

### 4.2 User Journey Tests Across Schools

**Test each user type performing workflows across multiple schools:**

```php
// tests/Integration/MultiSchoolUserJourneysTest.php

class MultiSchoolUserJourneysTest extends CIUnitTestCase
{
    /**
     * SuperAdmin Journey: Manage all 5 schools
     */
    public function testSuperAdminJourney()
    {
        // 1. Login as superadmin
        // 2. View all schools dashboard
        // 3. Switch to School 1
        // 4. Create new student in School 1
        // 5. Switch to School 2
        // 6. Create invoice in School 2
        // 7. View cross-school reports
        // 8. Verify data isolation
    }

    /**
     * School Admin Journey: Manage single school
     */
    public function testSchoolAdminJourney()
    {
        // 1. Login as school admin (Nairobi Primary)
        // 2. View dashboard (only Nairobi data)
        // 3. Create new class
        // 4. Enroll students
        // 5. Generate fee invoices
        // 6. View school reports
        // 7. Attempt to access another school (should fail)
    }

    /**
     * Teacher Journey: Teach in multiple schools
     */
    public function testMultiSchoolTeacherJourney()
    {
        // 1. Login as teacher (assigned to Schools 1 and 3)
        // 2. View school selector
        // 3. Switch to School 1
        // 4. View assigned classes in School 1
        // 5. Create assignment in School 1
        // 6. Switch to School 3
        // 7. View assigned classes in School 3
        // 8. Record attendance in School 3
        // 9. Verify cannot access School 2
    }

    /**
     * Student Journey: Single school access
     */
    public function testStudentJourney()
    {
        // 1. Login as student (enrolled in School 2)
        // 2. View classes and schedule
        // 3. Submit assignment
        // 4. Check grades
        // 5. View attendance
        // 6. Message teacher
        // 7. Verify cannot see other schools
    }

    /**
     * Parent Journey: Children in multiple schools
     */
    public function testParentMultiSchoolJourney()
    {
        // 1. Login as parent (child 1 in School 1, child 2 in School 2)
        // 2. View children selector
        // 3. Select child 1
        // 4. View School 1 data for child 1
        // 5. Check grades and attendance
        // 6. Switch to child 2
        // 7. View School 2 data for child 2
        // 8. Message teachers in both schools
    }
}
```

---

## Phase 5: Performance Optimization & Bug Fixes (45 minutes)

### 5.1 Database Optimization

```sql
-- Add indexes for tenant queries
CREATE INDEX idx_school_users_school ON school_users(school_id);
CREATE INDEX idx_school_users_user ON school_users(user_id);
CREATE INDEX idx_enrollments_school ON student_enrollments(school_id);
CREATE INDEX idx_enrollments_student ON student_enrollments(student_id);
CREATE INDEX idx_classes_school ON school_classes(school_id);

-- Add composite indexes
CREATE INDEX idx_school_user_lookup ON school_users(school_id, user_id, role_id);
CREATE INDEX idx_active_enrollments ON student_enrollments(school_id, status, student_id);
```

### 5.2 Caching Strategy

```php
// Cache school data
$schoolData = cache()->remember("school_{$schoolId}", 3600, function() use ($schoolId) {
    return model('SchoolModel')->find($schoolId);
});

// Cache user's schools
$userSchools = cache()->remember("user_{$userId}_schools", 1800, function() use ($userId) {
    return service('tenant')->getUserSchools($userId);
});

// Clear cache on updates
cache()->delete("school_{$schoolId}");
```

### 5.3 Query Optimization

```php
// Eager load relationships
$students = model('StudentModel')
    ->with('enrollment')
    ->with('class')
    ->with('parent')
    ->forSchool($schoolId)
    ->findAll();

// Use database views for complex queries
CREATE VIEW student_summary AS
SELECT 
    s.id,
    s.first_name,
    s.last_name,
    e.admission_number,
    e.status,
    c.class_name,
    sc.school_name
FROM ci4_users s
JOIN student_enrollments e ON s.id = e.student_id
JOIN school_classes c ON e.class_id = c.id
JOIN schools sc ON e.school_id = sc.id;
```

### 5.4 Bug Hunt & Fix

**Test and fix:**
1. Session persistence across school switches
2. Permission checks in tenant context
3. Foreign key constraint violations
4. Date/timezone handling per school
5. Currency formatting per school
6. File uploads in multi-tenant context
7. Report generation accuracy
8. Email notifications (correct school context)
9. API authentication with tenant context
10. Search functionality scoped to school

---

## Phase 6: Advanced Features (30 minutes)

### 6.1 Cross-School Analytics

```php
// For SuperAdmins and System Administrators
class CrossSchoolAnalyticsService
{
    public function getSystemWideStats(): array
    {
        return [
            'total_schools' => $this->getTotalSchools(),
            'total_students' => $this->getTotalStudents(),
            'total_teachers' => $this->getTotalTeachers(),
            'total_revenue' => $this->getTotalRevenue(),
            'schools_by_type' => $this->getSchoolsByType(),
            'subscription_breakdown' => $this->getSubscriptionBreakdown(),
            'top_performing_schools' => $this->getTopSchools(10),
            'schools_needing_attention' => $this->getUnderperformingSchools(5)
        ];
    }

    public function compareSchools(array $schoolIds): array
    {
        // Compare multiple schools across metrics
    }
}
```

### 6.2 School Onboarding Wizard

```php
// Complete school setup wizard
class SchoolOnboardingController extends BaseController
{
    public function step1BasicInfo() {} // School name, location, contact
    public function step2Subscription() {} // Choose plan
    public function step3AdminAccount() {} // Create school admin
    public function step4Classes() {} // Setup initial classes
    public function step5Teachers() {} // Invite teachers
    public function step6Students() {} // Import/add students
    public function step7Settings() {} // Configure school settings
    public function complete() {} // Finalize and activate
}
```

### 6.3 School Transfer/Migration

```php
// Transfer student between schools
class TransferService
{
    public function transferStudent(
        int $studentId, 
        int $fromSchoolId, 
        int $toSchoolId
    ): bool {
        // 1. Verify student exists in source school
        // 2. Close enrollment in source school
        // 3. Create enrollment in target school
        // 4. Transfer relevant records (grades, attendance)
        // 5. Notify both schools
        // 6. Update parent access
    }
}
```

---

## Phase 7: Testing & Coverage Validation (30 minutes)

### 7.1 Run Full Test Suite

```bash
# Run all tests
php vendor/bin/phpunit -c phpunit.ci4.xml --testdox

# Generate coverage report
php vendor/bin/phpunit --coverage-html coverage/ --coverage-text

# Coverage by module (must be >90% each)
php vendor/bin/phpunit tests/Foundation/ --coverage-text
php vendor/bin/phpunit tests/Hr/ --coverage-text
php vendor/bin/phpunit tests/Finance/ --coverage-text
php vendor/bin/phpunit tests/Learning/ --coverage-text
php vendor/bin/phpunit tests/Library/ --coverage-text
php vendor/bin/phpunit tests/Inventory/ --coverage-text
php vendor/bin/phpunit tests/Mobile/ --coverage-text
php vendor/bin/phpunit tests/Threads/ --coverage-text
```

### 7.2 Coverage Targets (MUST ACHIEVE)

| Module | Current | Target | Status |
|--------|---------|--------|--------|
| Foundation | 78% | >90% | ❌ TO DO |
| Finance | 92% | >90% | ✅ PASS |
| Learning | 88% | >90% | ❌ TO DO |
| Library | 90% | >90% | ✅ PASS |
| Inventory | 85% | >90% | ❌ TO DO |
| Mobile | 87% | >90% | ❌ TO DO |
| Threads | 91% | >90% | ✅ PASS |
| HR | 89% | >90% | ❌ TO DO |
| **OVERALL** | **87.8%** | **>90%** | ❌ TO DO |

### 7.3 Quality Gates

**All must pass:**
- ✅ 100% of tests passing
- ✅ >90% code coverage per module
- ✅ Zero critical security issues
- ✅ Zero database query errors
- ✅ All API endpoints documented
- ✅ PSR-12 code style compliance
- ✅ PHPStan level 8 passing
- ✅ No deprecated code

---

## Phase 8: Documentation & Deployment (20 minutes)

### 8.1 Multi-School Documentation

**Create**:
1. `MULTI_SCHOOL_GUIDE.md` - Complete multi-school setup guide
2. `SCHOOL_ADMIN_MANUAL.md` - School administrator manual
3. `API_MULTI_TENANT.md` - Multi-tenant API documentation
4. `DEPLOYMENT_MULTI_SCHOOL.md` - Production deployment guide

### 8.2 Update Existing Documentation

**Update**:
- `TESTING.md` - Add all 195 user credentials
- `README.md` - Add multi-school features
- `BUILD_VALIDATION_REPORT.md` - Update with final metrics
- `SESSION_CHANGELOG.md` - Document all changes

### 8.3 Create User Guides

**For each role**:
- SuperAdmin Guide
- School Admin Guide
- Teacher Guide (single & multi-school)
- Student Guide
- Parent Guide (single & multi-child)

---

## Deliverables Checklist

### Code
- [ ] 4 new database migrations (schools, school_users, classes, enrollments)
- [ ] TenantService with full multi-school logic
- [ ] TenantModel base class for auto-scoping
- [ ] TenantFilter for request-level tenant resolution
- [ ] 8 modules fully implemented with >90% coverage each
- [ ] 195+ test users across 5 schools
- [ ] 200+ automated tests (all passing)
- [ ] API endpoints (all tenant-aware)
- [ ] Cross-school analytics dashboard

### Testing
- [ ] Unit tests for all services
- [ ] Integration tests for tenant isolation
- [ ] API tests for all endpoints
- [ ] User journey tests for all 5 role types
- [ ] Performance tests
- [ ] Security tests
- [ ] >90% coverage achieved for ALL modules

### Documentation
- [ ] Multi-school setup guide
- [ ] API documentation (OpenAPI spec)
- [ ] Admin manuals (system & school level)
- [ ] User guides for all roles
- [ ] Updated README and testing docs
- [ ] Deployment guide

### Quality
- [ ] All tests passing (200+ tests, 100% pass rate)
- [ ] >90% code coverage (all modules)
- [ ] PHPStan level 8 passing
- [ ] PHP-CS-Fixer compliant
- [ ] Zero security vulnerabilities
- [ ] Performance optimized (queries, caching)

---

## Success Criteria

### Functional Requirements
✅ 5 schools created with diverse configurations  
✅ 195+ users across all schools and roles  
✅ Complete tenant isolation (no data leakage)  
✅ Multi-school user access working  
✅ School switching functional  
✅ All 8 modules operational per school  
✅ Cross-school analytics for SuperAdmin  
✅ API fully multi-tenant aware  

### Testing Requirements
✅ 200+ automated tests  
✅ 100% test pass rate  
✅ >90% coverage for Foundation module  
✅ >90% coverage for HR module  
✅ >90% coverage for Finance module  
✅ >90% coverage for Learning module  
✅ >90% coverage for Library module  
✅ >90% coverage for Inventory module  
✅ >90% coverage for Mobile module  
✅ >90% coverage for Threads module  
✅ Tenant isolation verified  
✅ All user journeys tested  

### Quality Requirements
✅ PHPStan level 8 - zero errors  
✅ PSR-12 compliant  
✅ Zero security vulnerabilities  
✅ API response time <200ms (p95)  
✅ Database queries optimized  
✅ Proper indexing in place  

### Documentation Requirements
✅ Complete API documentation  
✅ Multi-school setup guide  
✅ Admin manuals  
✅ User guides (all roles)  
✅ Deployment guide  
✅ All test credentials documented  

---

## Estimated Timeline

| Phase | Duration | Cumulative |
|-------|----------|------------|
| 1. Multi-School Architecture | 45 min | 0:45 |
| 2. Test Data Generation | 30 min | 1:15 |
| 3. Module Implementation | 120 min | 3:15 |
| 4. Cross-School Testing | 60 min | 4:15 |
| 5. Optimization & Bugs | 45 min | 5:00 |
| 6. Advanced Features | 30 min | 5:30 |
| 7. Coverage Validation | 30 min | 6:00 |
| 8. Documentation | 20 min | 6:20 |

**Total Estimated Time**: 6 hours 20 minutes

---

## Final Validation Commands

```bash
# 1. Run full test suite
php vendor/bin/phpunit -c phpunit.ci4.xml --testdox

# 2. Generate coverage report
php vendor/bin/phpunit --coverage-html coverage/ --coverage-text

# 3. Verify coverage >90% for each module
php vendor/bin/phpunit tests/Foundation/ --coverage-text | grep "Lines:"
php vendor/bin/phpunit tests/Hr/ --coverage-text | grep "Lines:"
php vendor/bin/phpunit tests/Finance/ --coverage-text | grep "Lines:"
php vendor/bin/phpunit tests/Learning/ --coverage-text | grep "Lines:"
php vendor/bin/phpunit tests/Library/ --coverage-text | grep "Lines:"
php vendor/bin/phpunit tests/Inventory/ --coverage-text | grep "Lines:"
php vendor/bin/phpunit tests/Mobile/ --coverage-text | grep "Lines:"
php vendor/bin/phpunit tests/Threads/ --coverage-text | grep "Lines:"

# 4. Static analysis
php vendor/bin/phpstan analyse --level=8

# 5. Code style
php vendor/bin/php-cs-fixer fix --dry-run

# 6. Security scan
composer audit

# 7. Database check
php spark migrate:status
php spark db:seed --class="MultiSchoolSeeder"

# 8. Performance test
ab -n 1000 -c 10 http://localhost:8080/api/v1/schools

# 9. API documentation
php spark openapi:generate

# 10. Final report
php spark report:generate
```

---

## Expected Final Metrics

```
╔═══════════════════════════════════════════════════════════╗
║           SHULELABS CI4 - FINAL BUILD REPORT              ║
╠═══════════════════════════════════════════════════════════╣
║ SCHOOLS CREATED:              5 schools                   ║
║ USERS GENERATED:              195 users                   ║
║ TOTAL TESTS:                  200+ tests                  ║
║ TESTS PASSING:                100%                        ║
║ OVERALL COVERAGE:             >90%                        ║
║ FOUNDATION MODULE:            >90%                        ║
║ HR MODULE:                    >90%                        ║
║ FINANCE MODULE:               >90%                        ║
║ LEARNING MODULE:              >90%                        ║
║ LIBRARY MODULE:               >90%                        ║
║ INVENTORY MODULE:             >90%                        ║
║ MOBILE MODULE:                >90%                        ║
║ THREADS MODULE:               >90%                        ║
║ CODE QUALITY:                 A+                          ║
║ SECURITY GRADE:               A+                          ║
║ PERFORMANCE:                  A                           ║
║ API RESPONSE TIME (P95):      <200ms                      ║
║ DATABASE QUERIES:             Optimized                   ║
║ DOCUMENTATION:                Complete                    ║
║ STATUS:                       🎉 PRODUCTION READY         ║
╚═══════════════════════════════════════════════════════════╝
```

---

## GO! Execute This Prompt

**To AI Agent**: You are now authorized to execute this comprehensive plan. Work systematically through each phase, implement all features, write all tests, fix all bugs, optimize performance, and document everything. Your goal is a production-ready multi-school system with >90% test coverage across all modules.

**START NOW!**
