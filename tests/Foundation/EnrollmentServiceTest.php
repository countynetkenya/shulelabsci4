<?php

namespace Tests\Foundation;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Services\EnrollmentService;

/**
 * @internal
 */
final class EnrollmentServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = false;
    protected EnrollmentService $service;
    protected static bool $migrated = false;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Run migrations only once for all tests
        if (!self::$migrated) {
            $migrate = \Config\Services::migrations();
            $migrate->latest();
            self::$migrated = true;
        }
        
        $this->service = new EnrollmentService();
        
        // Create minimal test data
        $db = \Config\Database::connect();
        
        // Create school if not exists
        $existing = $db->table('schools')->where('id', 6)->get()->getRow();
        if (!$existing) {
            $db->table('schools')->insert([
                'id' => 6,
                'school_name' => 'Test School',
                'school_code' => 'TEST001',
                'max_students' => 1000,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        
        // Create classes if not exist
        $existingClass = $db->table('school_classes')->where('id', 1)->get()->getRow();
        if (!$existingClass) {
            $db->table('school_classes')->insertBatch([
                ['id' => 1, 'school_id' => 6, 'class_name' => 'Class 1', 'max_capacity' => 40, 'created_at' => date('Y-m-d H:i:s')],
                ['id' => 2, 'school_id' => 6, 'class_name' => 'Class 2', 'max_capacity' => 40, 'created_at' => date('Y-m-d H:i:s')],
            ]);
        }
        
        // Create users if not exist
        $existingUser = $db->table('ci4_users')->where('id', 218)->get()->getRow();
        if (!$existingUser) {
            $db->table('ci4_users')->insertBatch([
                ['id' => 218, 'username' => 'student218', 'email' => 'student218@test.com', 'full_name' => 'Student 218', 'password_hash' => password_hash('password', PASSWORD_DEFAULT), 'created_at' => date('Y-m-d H:i:s')],
                ['id' => 219, 'username' => 'student219', 'email' => 'student219@test.com', 'full_name' => 'Student 219', 'password_hash' => password_hash('password', PASSWORD_DEFAULT), 'created_at' => date('Y-m-d H:i:s')],
                ['id' => 33, 'username' => 'student33', 'email' => 'student33@test.com', 'full_name' => 'Student 33', 'password_hash' => password_hash('password', PASSWORD_DEFAULT), 'created_at' => date('Y-m-d H:i:s')],
                ['id' => 134, 'username' => 'parent134', 'email' => 'parent134@test.com', 'full_name' => 'Parent 134', 'password_hash' => password_hash('password', PASSWORD_DEFAULT), 'created_at' => date('Y-m-d H:i:s')],
                ['id' => 220, 'username' => 'student220', 'email' => 'student220@test.com', 'full_name' => 'Student 220', 'password_hash' => password_hash('password', PASSWORD_DEFAULT), 'created_at' => date('Y-m-d H:i:s')],
            ]);
        }
        
        // Pre-enroll student 33 to test duplicate enrollment
        $enrollment = $db->table('student_enrollments')->where('student_id', 33)->where('school_id', 6)->get()->getRow();
        if (!$enrollment) {
            $db->table('student_enrollments')->insert([
                'student_id' => 33,
                'school_id' => 6,
                'class_id' => 1,
                'status' => 'active',
                'enrollment_date' => date('Y-m-d'),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function testEnrollStudent(): void
    {
        // Enroll new student (ID 218) to School 6, Class 1
        $result = $this->service->enrollStudent(6, 218, 1);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('enrollment_id', $result);
    }

    public function testEnrollStudentWithParent(): void
    {
        // Enroll student with parent link
        $result = $this->service->enrollStudent(6, 219, 1, 134); // parent_id = 134

        $this->assertTrue($result['success']);
    }

    public function testEnrollStudentInNonExistentClass(): void
    {
        $result = $this->service->enrollStudent(6, 218, 999); // Class 999 doesn't exist

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('not found', $result['message']);
    }

    public function testCannotEnrollDuplicateStudent(): void
    {
        // Try to enroll student 33 who is already enrolled in School 6
        $result = $this->service->enrollStudent(6, 33, 1);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('already enrolled', $result['message']);
    }

    public function testTransferClass(): void
    {
        // Get an existing enrollment from School 6
        $enrollment = model('StudentEnrollmentModel')->forSchool(6)->first();
        
        $this->assertNotNull($enrollment);

        // Transfer to different class (from class 1 to class 2)
        $result = $this->service->transferClass($enrollment['id'], 2);

        $this->assertTrue($result['success']);
    }

    public function testTransferToNonExistentClass(): void
    {
        $enrollment = model('StudentEnrollmentModel')->forSchool(6)->first();

        $result = $this->service->transferClass($enrollment['id'], 999);

        $this->assertFalse($result['success']);
    }

    public function testGetClassEnrollments(): void
    {
        // Get enrollments for class 1 in School 6
        $enrollments = $this->service->getClassEnrollments(1);

        $this->assertIsArray($enrollments);
        $this->assertGreaterThan(0, count($enrollments));
        
        foreach ($enrollments as $enrollment) {
            $this->assertArrayHasKey('username', $enrollment);
            $this->assertArrayHasKey('email', $enrollment);
            $this->assertArrayHasKey('full_name', $enrollment);
        }
    }

    public function testWithdrawStudent(): void
    {
        // Create a new enrollment to withdraw
        $result = $this->service->enrollStudent(6, 220, 1);
        $this->assertTrue($result['success']);

        $enrollmentId = $result['enrollment_id'];

        // Withdraw the student
        $withdrawResult = $this->service->withdrawStudent($enrollmentId, 'Test withdrawal');

        $this->assertTrue($withdrawResult['success']);

        // Verify status changed
        $enrollment = model('StudentEnrollmentModel')->find($enrollmentId);
        $this->assertEquals('withdrawn', $enrollment['status']);
    }

    public function testWithdrawNonExistentEnrollment(): void
    {
        $result = $this->service->withdrawStudent(99999);

        $this->assertFalse($result['success']);
    }

    public function testGetClassEnrollmentsExcludesWithdrawn(): void
    {
        $enrollments = $this->service->getClassEnrollments(1);

        foreach ($enrollments as $enrollment) {
            $this->assertEquals('active', $enrollment['status']);
        }
    }
}
