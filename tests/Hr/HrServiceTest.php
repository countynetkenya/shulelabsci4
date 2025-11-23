<?php

namespace Tests\Hr;

use App\Services\HrService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class HrServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = false;

    protected HrService $service;

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

        $this->service = new HrService();

        // Create minimal test data
        $db = \Config\Database::connect();

        // Create schools
        $schools = [
            ['id' => 6, 'school_name' => 'Nairobi Primary', 'school_code' => 'NRB001', 'max_students' => 500],
            ['id' => 7, 'school_name' => 'Mombasa Secondary', 'school_code' => 'MSA001', 'max_students' => 600],
        ];

        foreach ($schools as $school) {
            $existing = $db->table('schools')->where('id', $school['id'])->get()->getRow();
            if (!$existing) {
                $school['created_at'] = date('Y-m-d H:i:s');
                $db->table('schools')->insert($school);
            }
        }

        // Create classes
        for ($i = 1; $i <= 3; $i++) {
            $existing = $db->table('school_classes')->where('id', $i)->get()->getRow();
            if (!$existing) {
                $db->table('school_classes')->insert([
                    'id' => $i,
                    'school_id' => 6,
                    'class_name' => "Grade {$i}",
                    'max_capacity' => 40,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        // Create users (teachers and staff) - includes IDs referenced in tests
        $teacherIds = [25, 26, 101, 102, 103, 104, 105];
        foreach ($teacherIds as $i) {
            $existing = $db->table('users')->where('id', $i)->get()->getRow();
            if (!$existing) {
                $db->table('users')->insert([
                    'id' => $i,
                    'username' => "teacher{$i}",
                    'email' => "teacher{$i}@test.com",
                    'full_name' => "Teacher {$i}",
                    'password_hash' => password_hash('password', PASSWORD_DEFAULT),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        // Assign teachers to school
        foreach ($teacherIds as $i) {
            $existing = $db->table('school_users')->where('user_id', $i)->where('school_id', 6)->get()->getRow();
            if (!$existing) {
                $db->table('school_users')->insert([
                    'school_id' => 6,
                    'user_id' => $i,
                    'role_id' => 3, // Teacher role
                    'is_primary_school' => 1,
                    'joined_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        // Assign teacher 25 to class 1 (for testGetTeacherClasses)
        $existing = $db->table('school_classes')->where('id', 1)->get()->getRow();
        if ($existing && !$existing->class_teacher_id) {
            $db->table('school_classes')->where('id', 1)->update(['class_teacher_id' => 25]);
        }
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
