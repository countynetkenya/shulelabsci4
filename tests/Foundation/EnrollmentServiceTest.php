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

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EnrollmentService();
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
