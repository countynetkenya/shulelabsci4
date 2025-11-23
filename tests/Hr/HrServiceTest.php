<?php

namespace Tests\Hr;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Services\HrService;

/**
 * @internal
 */
final class HrServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = false;
    protected HrService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new HrService();
    }

    public function testGetSchoolStaff(): void
    {
        $staff = $this->service->getSchoolStaff(6); // Nairobi Primary

        $this->assertIsArray($staff);
        $this->assertGreaterThan(0, count($staff));
        
        // Should have teachers and admins, not students
        foreach ($staff as $member) {
            $this->assertNotEquals('Student', $member['role_name']);
            $this->assertNotEquals('Parent', $member['role_name']);
        }
    }

    public function testGetSchoolStaffWithRoleFilter(): void
    {
        $teachers = $this->service->getSchoolStaff(6, 'Teacher');

        $this->assertIsArray($teachers);
        
        foreach ($teachers as $teacher) {
            $this->assertEquals('Teacher', $teacher['role_name']);
        }
    }

    public function testAssignTeacher(): void
    {
        // Create new user for testing with unique username
        $timestamp = time();
        $userId = model('App\Models\UserModel')->insert([
            'username' => 'newteacher' . $timestamp,
            'email' => 'newteacher' . $timestamp . '@test.local',
            'password_hash' => password_hash('test123', PASSWORD_BCRYPT),
            'full_name' => 'New Teacher',
        ]);

        // Get teacher role ID (ID 2 is Teacher)
        $result = $this->service->assignTeacher($userId, 6, 2, false);

        $this->assertTrue($result['success']);
    }

    public function testCannotAssignTeacherTwice(): void
    {
        // Try to assign existing teacher (ID 25 is already in school 6)
        $result = $this->service->assignTeacher(25, 6, 2, false);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('already assigned', $result['message']);
    }

    public function testRemoveTeacher(): void
    {
        // First assign a teacher with unique username
        $timestamp = time();
        $userId = model('App\Models\UserModel')->insert([
            'username' => 'tempteacher' . $timestamp,
            'email' => 'tempteacher' . $timestamp . '@test.local',
            'password_hash' => password_hash('test123', PASSWORD_BCRYPT),
            'full_name' => 'Temp Teacher',
        ]);

        $this->service->assignTeacher($userId, 6, 2, false);

        // Now remove
        $result = $this->service->removeTeacher($userId, 6);

        $this->assertTrue($result['success']);
    }

    public function testGetTeacherClasses(): void
    {
        // Teacher 25 is assigned to class 1 in school 6
        $classes = $this->service->getTeacherClasses(25, 6);

        $this->assertIsArray($classes);
        $this->assertGreaterThan(0, count($classes));
    }

    public function testAssignTeacherToClass(): void
    {
        // Assign teacher 26 to class 2 in school 6
        $result = $this->service->assignTeacherToClass(2, 26, 6);

        $this->assertTrue($result['success']);

        // Verify assignment
        $class = model('App\Models\SchoolClassModel')->find(2);
        $this->assertEquals(26, $class['class_teacher_id']);
    }

    public function testCannotAssignTeacherToClassInDifferentSchool(): void
    {
        // Try to assign teacher from school 6 to class in school 7
        $result = $this->service->assignTeacherToClass(7, 25, 7); // Teacher 25 is not in school 7

        $this->assertFalse($result['success']);
    }

    public function testGetStaffStats(): void
    {
        $stats = $this->service->getStaffStats(6);

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_staff', $stats);
        $this->assertArrayHasKey('by_role', $stats);
        $this->assertIsArray($stats['by_role']);
        $this->assertGreaterThan(0, $stats['total_staff']);
    }

    public function testStaffStatsExcludesStudentsAndParents(): void
    {
        $stats = $this->service->getStaffStats(6);

        $this->assertArrayNotHasKey('Student', $stats['by_role']);
        $this->assertArrayNotHasKey('Parent', $stats['by_role']);
    }
}
