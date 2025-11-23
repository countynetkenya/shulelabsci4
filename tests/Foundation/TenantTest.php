<?php

namespace Tests\Foundation;

use App\Services\TenantService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * TenantTest - Test tenant isolation and multi-school functionality.
 *
 * Coverage:
 * - Tenant context management
 * - School switching
 * - Multi-school access
 * - Automatic query scoping
 * - Tenant isolation verification
 *
 * Target: 95% coverage
 */
class TenantTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $refresh = false;
    protected $namespace = 'App';
    protected static bool $migrated = false;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Run migrations only once for all tests
        if (!self::$migrated) {
            $migrate = \Config\Services::migrations();
            $migrate->latest();
            self::$migrated = true;
            
            // Seed test data for multi-school tenant tests
            $this->seedTestData();
        }
    }
    
    /**
     * Seed comprehensive test data for tenant isolation tests
     */
    protected function seedTestData(): void
    {
        $db = \Config\Database::connect();
        
        // Create schools
        $db->table('schools')->insertBatch([
            [
                'school_id'   => 1,
                'school_name' => 'Test School 1',
                'school_code' => 'TS001',
                'status'      => 'active',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'   => 2,
                'school_name' => 'Test School 2',
                'school_code' => 'TS002',
                'status'      => 'active',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'   => 3,
                'school_name' => 'Test School 3',
                'school_code' => 'TS003',
                'status'      => 'inactive',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
        ]);
        
        // Create roles
        $db->table('roles')->insertBatch([
            ['role_id' => 1, 'role_name' => 'SuperAdmin', 'created_at' => date('Y-m-d H:i:s')],
            ['role_id' => 2, 'role_name' => 'SchoolAdmin', 'created_at' => date('Y-m-d H:i:s')],
            ['role_id' => 3, 'role_name' => 'Teacher', 'created_at' => date('Y-m-d H:i:s')],
            ['role_id' => 4, 'role_name' => 'Student', 'created_at' => date('Y-m-d H:i:s')],
        ]);
        
        // Create test users
        $db->table('users')->insertBatch([
            [
                'user_id'       => 24,
                'username'      => 'schooladmin100',
                'email'         => 'admin1@test.local',
                'password_hash' => password_hash('Test@123', PASSWORD_BCRYPT),
                'full_name'     => 'School Admin 1',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
                'is_active'     => 1,
            ],
            [
                'user_id'       => 64,
                'username'      => 'schooladmin200',
                'email'         => 'admin2@test.local',
                'password_hash' => password_hash('Test@123', PASSWORD_BCRYPT),
                'full_name'     => 'School Admin 2',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
                'is_active'     => 1,
            ],
        ]);
        
        // Create user-school mappings
        $db->table('school_users')->insertBatch([
            [
                'user_id'           => 24,
                'school_id'         => 1,
                'role_id'           => 2, // SchoolAdmin
                'is_primary_school' => 1,
                'joined_at'         => date('Y-m-d H:i:s'),
            ],
            [
                'user_id'           => 64,
                'school_id'         => 2,
                'role_id'           => 2, // SchoolAdmin
                'is_primary_school' => 1,
                'joined_at'         => date('Y-m-d H:i:s'),
            ],
        ]);
        
        // Create sample enrollment data for tenant isolation testing
        $db->table('student_enrollments')->insertBatch([
            [
                'school_id'      => 1,
                'student_id'     => 101,
                'class_id'       => 1,
                'enrollment_date' => date('Y-m-d'),
                'status'         => 'active',
                'created_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'      => 1,
                'student_id'     => 102,
                'class_id'       => 1,
                'enrollment_date' => date('Y-m-d'),
                'status'         => 'active',
                'created_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'      => 2,
                'student_id'     => 201,
                'class_id'       => 2,
                'enrollment_date' => date('Y-m-d'),
                'status'         => 'active',
                'created_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'      => 2,
                'student_id'     => 202,
                'class_id'       => 2,
                'enrollment_date' => date('Y-m-d'),
                'status'         => 'active',
                'created_at'     => date('Y-m-d H:i:s'),
            ],
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    // ============================================================
    // TENANT SERVICE TESTS
    // ============================================================

    public function testTenantServiceExists()
    {
        $service = service('tenant');
        $this->assertInstanceOf(TenantService::class, $service);
    }

    public function testSetCurrentSchoolBySession()
    {
        $tenantService = service('tenant');
        session()->set('current_school_id', 1);

        $currentSchool = $tenantService->getCurrentSchool();
        $this->assertEquals(1, $currentSchool);
    }

    public function testSetCurrentSchoolByPrimarySchool()
    {
        // User 24 (schooladmin100) has school 1 as primary
        $tenantService = service('tenant');
        $userId = 24;

        $schoolId = $tenantService->setCurrentSchool($userId);
        $this->assertEquals(1, $schoolId);
    }

    public function testSwitchSchool()
    {
        $tenantService = service('tenant');
        $userId = 24; // schooladmin100

        // Switch to school 1
        $success = $tenantService->switchSchool(1, $userId);
        $this->assertTrue($success);
        $this->assertEquals(1, session()->get('current_school_id'));
    }

    public function testSwitchSchoolFailsForUnauthorizedUser()
    {
        $tenantService = service('tenant');
        $userId = 24; // Only has access to school 1

        // Try to switch to school 2 (no access)
        $success = $tenantService->switchSchool(2, $userId);
        $this->assertFalse($success);
    }

    public function testGetUserSchools()
    {
        $tenantService = service('tenant');
        $userId = 24; // schooladmin100 - only in school 1

        $schools = $tenantService->getUserSchools($userId);
        $this->assertIsArray($schools);
        $this->assertCount(1, $schools);
        $this->assertEquals(1, $schools[0]->school_id);
    }

    public function testHasAccessToSchool()
    {
        $tenantService = service('tenant');
        $userId = 24; // schooladmin100

        // Has access to school 1
        $this->assertTrue($tenantService->hasAccessToSchool($userId, 1));

        // Does NOT have access to school 2
        $this->assertFalse($tenantService->hasAccessToSchool($userId, 2));
    }

    // ============================================================
    // TENANT MODEL AUTO-SCOPING TESTS
    // ============================================================

    public function testTenantModelAutoScoping()
    {
        $tenantService = service('tenant');
        $tenantService->switchSchool(1, 24); // Set to school 1

        $enrollmentModel = model('StudentEnrollmentModel');

        // This should automatically scope to school 1
        $enrollments = $enrollmentModel->findAll();

        // Should only get school 1 enrollments
        foreach ($enrollments as $enrollment) {
            $this->assertEquals(1, $enrollment->school_id);
        }
    }

    public function testTenantModelForSchool()
    {
        $enrollmentModel = model('StudentEnrollmentModel');

        // Query specific school
        $enrollments = $enrollmentModel->forSchool(2)->findAll();

        foreach ($enrollments as $enrollment) {
            $this->assertEquals(2, $enrollment->school_id);
        }
    }

    public function testTenantModelWithoutTenant()
    {
        $enrollmentModel = model('StudentEnrollmentModel');

        // Query all schools (SuperAdmin)
        $allEnrollments = $enrollmentModel->withoutTenant()->findAll();

        $schoolIds = array_unique(array_column($allEnrollments, 'school_id'));
        $this->assertGreaterThan(1, count($schoolIds)); // Multiple schools
    }

    public function testTenantModelForSchools()
    {
        $enrollmentModel = model('StudentEnrollmentModel');

        // Query multiple schools
        $enrollments = $enrollmentModel->forSchools([1, 2])->findAll();

        $schoolIds = array_unique(array_column($enrollments, 'school_id'));
        $this->assertContains(1, $schoolIds);
        $this->assertContains(2, $schoolIds);
        $this->assertNotContains(3, $schoolIds);
    }

    // ============================================================
    // TENANT ISOLATION TESTS
    // ============================================================

    public function testSchool1CannotSeeSchool2Data()
    {
        $tenantService = service('tenant');
        $tenantService->switchSchool(1, 24);

        $enrollmentModel = model('StudentEnrollmentModel');
        $school1Enrollments = $enrollmentModel->findAll();

        foreach ($school1Enrollments as $enrollment) {
            $this->assertNotEquals(2, $enrollment->school_id);
            $this->assertEquals(1, $enrollment->school_id);
        }
    }

    public function testDataLeakagePreventionBetweenSchools()
    {
        $tenantService = service('tenant');

        // Switch to school 1
        $tenantService->switchSchool(1, 24);
        $enrollmentModel = model('StudentEnrollmentModel');
        $school1Data = $enrollmentModel->findAll();

        // Switch to school 2
        $tenantService->switchSchool(2, 64); // Assuming user 64 has access to school 2
        $school2Data = $enrollmentModel->findAll();

        // Ensure no overlap (assuming data exists for both)
        $school1Ids = array_column($school1Data, 'id');
        $school2Ids = array_column($school2Data, 'id');

        $this->assertEmpty(array_intersect($school1Ids, $school2Ids));
    }

    // ============================================================
    // MULTI-SCHOOL USER ACCESS TESTS
    // ============================================================

    public function testUserCanBelongToMultipleSchools()
    {
        // Create a multi-school user (e.g., teacher in 2 schools)
        $db = \Config\Database::connect();

        $userId = $db->table('users')->insert([
            'username'      => 'multiteacher',
            'email'         => 'multiteacher@test.local',
            'password_hash' => password_hash('Test@123', PASSWORD_BCRYPT),
            'full_name'     => 'Multi School Teacher',
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
            'is_active'     => 1,
        ]);
        $userId = $db->insertID();

        // Assign to school 1
        $db->table('school_users')->insert([
            'user_id'           => $userId,
            'school_id'         => 1,
            'role_id'           => 3, // Teacher
            'is_primary_school' => 1,
            'joined_at'         => date('Y-m-d H:i:s'),
        ]);

        // Assign to school 2
        $db->table('school_users')->insert([
            'user_id'           => $userId,
            'school_id'         => 2,
            'role_id'           => 3, // Teacher
            'is_primary_school' => 0,
            'joined_at'         => date('Y-m-d H:i:s'),
        ]);

        $tenantService = service('tenant');
        $schools = $tenantService->getUserSchools($userId);

        $this->assertCount(2, $schools);
        $schoolIds = array_column($schools, 'school_id');
        $this->assertContains(1, $schoolIds);
        $this->assertContains(2, $schoolIds);

        // Cleanup
        $db->table('school_users')->where('user_id', $userId)->delete();
        $db->table('users')->where('id', $userId)->delete();
    }

    public function testPrimarySchoolIdentification()
    {
        $db = \Config\Database::connect();

        $result = $db->table('school_users')
            ->where('user_id', 24) // schooladmin100
            ->where('is_primary_school', 1)
            ->get()
            ->getRow();

        $this->assertNotNull($result);
        $this->assertEquals(1, $result->school_id);
    }

    // ============================================================
    // SCHOOL MODEL TESTS
    // ============================================================

    public function testGetActiveSchools()
    {
        $schoolModel = model('SchoolModel');
        $schools = $schoolModel->getActiveSchools();

        $this->assertCount(5, $schools);
        foreach ($schools as $school) {
            $this->assertEquals('active', $school->status);
        }
    }

    public function testGetSchoolByCode()
    {
        $schoolModel = model('SchoolModel');
        $school = $schoolModel->getByCode('NPS001');

        $this->assertNotNull($school);
        $this->assertEquals('Nairobi Primary School', $school->school_name);
    }

    public function testGetSchoolStatistics()
    {
        $schoolModel = model('SchoolModel');
        $stats = $schoolModel->getStatistics(1);

        $this->assertArrayHasKey('total_users', $stats);
        $this->assertEquals(40, $stats['total_users']); // School 1 has 40 users
    }

    // ============================================================
    // SCHOOL USER MODEL TESTS
    // ============================================================

    public function testAssignUserToSchool()
    {
        $db = \Config\Database::connect();

        // Create test user
        $userId = $db->table('users')->insert([
            'username'      => 'testuser_assign',
            'email'         => 'testuser_assign@test.local',
            'password_hash' => password_hash('Test@123', PASSWORD_BCRYPT),
            'full_name'     => 'Test User Assign',
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
            'is_active'     => 1,
        ]);
        $userId = $db->insertID();

        $schoolUserModel = model('SchoolUserModel');
        $result = $schoolUserModel->assignUserToSchool($userId, 3, 4); // Student role

        $this->assertTrue($result);

        // Verify assignment
        $assignment = $db->table('school_users')
            ->where('user_id', $userId)
            ->where('school_id', 3)
            ->get()
            ->getRow();

        $this->assertNotNull($assignment);
        $this->assertEquals(4, $assignment->role_id); // Student

        // Cleanup
        $db->table('school_users')->where('user_id', $userId)->delete();
        $db->table('users')->where('id', $userId)->delete();
    }

    public function testSetPrimarySchool()
    {
        $db = \Config\Database::connect();

        // Create test user with 2 schools
        $userId = $db->table('users')->insert([
            'username'      => 'testuser_primary',
            'email'         => 'testuser_primary@test.local',
            'password_hash' => password_hash('Test@123', PASSWORD_BCRYPT),
            'full_name'     => 'Test User Primary',
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
            'is_active'     => 1,
        ]);
        $userId = $db->insertID();

        // Assign to school 1
        $db->table('school_users')->insert([
            'user_id'           => $userId,
            'school_id'         => 1,
            'role_id'           => 3,
            'is_primary_school' => 1,
            'joined_at'         => date('Y-m-d H:i:s'),
        ]);

        // Assign to school 2
        $db->table('school_users')->insert([
            'user_id'           => $userId,
            'school_id'         => 2,
            'role_id'           => 3,
            'is_primary_school' => 0,
            'joined_at'         => date('Y-m-d H:i:s'),
        ]);

        $schoolUserModel = model('SchoolUserModel');
        $result = $schoolUserModel->setPrimarySchool($userId, 2);

        $this->assertTrue($result);

        // Verify only school 2 is primary
        $school1 = $db->table('school_users')
            ->where('user_id', $userId)
            ->where('school_id', 1)
            ->get()
            ->getRow();
        $this->assertEquals(0, $school1->is_primary_school);

        $school2 = $db->table('school_users')
            ->where('user_id', $userId)
            ->where('school_id', 2)
            ->get()
            ->getRow();
        $this->assertEquals(1, $school2->is_primary_school);

        // Cleanup
        $db->table('school_users')->where('user_id', $userId)->delete();
        $db->table('users')->where('id', $userId)->delete();
    }
}
