<?php

namespace Tests\Foundation;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Services\SchoolService;

/**
 * @internal
 */
final class SchoolServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = false;
    protected SchoolService $service;
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
        
        $this->service = new SchoolService();
        
        // Create test data
        $db = \Config\Database::connect();
        
        // Create schools
        $schools = [
            ['id' => 6, 'school_name' => 'Nairobi Primary', 'school_code' => 'NRB001', 'max_students' => 500],
            ['id' => 7, 'school_name' => 'Mombasa Secondary', 'school_code' => 'MSA001', 'max_students' => 600],
            ['id' => 8, 'school_name' => 'Kisumu Academy', 'school_code' => 'KSM001', 'max_students' => 400],
            ['id' => 9, 'school_name' => 'Eldoret High', 'school_code' => 'ELD001', 'max_students' => 550],
            ['id' => 10, 'school_name' => 'Nakuru Primary', 'school_code' => 'NAK001', 'max_students' => 450],
        ];
        
        foreach ($schools as $school) {
            $existing = $db->table('schools')->where('id', $school['id'])->get()->getRow();
            if (!$existing) {
                $school['created_at'] = date('Y-m-d H:i:s');
                $db->table('schools')->insert($school);
            }
        }
        
        // Create classes - 6 for school 6, 8 for school 7
        $classes = [
            // Nairobi Primary (school 6) - 6 classes
            ['id' => 1, 'school_id' => 6, 'class_name' => 'Grade 1A', 'grade_level' => '1', 'section' => 'A', 'max_capacity' => 40],
            ['id' => 2, 'school_id' => 6, 'class_name' => 'Grade 2A', 'grade_level' => '2', 'section' => 'A', 'max_capacity' => 40],
            ['id' => 3, 'school_id' => 6, 'class_name' => 'Grade 3A', 'grade_level' => '3', 'section' => 'A', 'max_capacity' => 40],
            ['id' => 4, 'school_id' => 6, 'class_name' => 'Grade 4A', 'grade_level' => '4', 'section' => 'A', 'max_capacity' => 40],
            ['id' => 5, 'school_id' => 6, 'class_name' => 'Grade 5A', 'grade_level' => '5', 'section' => 'A', 'max_capacity' => 40],
            ['id' => 6, 'school_id' => 6, 'class_name' => 'Grade 6A', 'grade_level' => '6', 'section' => 'A', 'max_capacity' => 40],
            // Mombasa Secondary (school 7) - 8 classes
            ['id' => 7, 'school_id' => 7, 'class_name' => 'Form 1A', 'grade_level' => '9', 'section' => 'A', 'max_capacity' => 45],
            ['id' => 8, 'school_id' => 7, 'class_name' => 'Form 1B', 'grade_level' => '9', 'section' => 'B', 'max_capacity' => 45],
            ['id' => 9, 'school_id' => 7, 'class_name' => 'Form 2A', 'grade_level' => '10', 'section' => 'A', 'max_capacity' => 45],
            ['id' => 10, 'school_id' => 7, 'class_name' => 'Form 2B', 'grade_level' => '10', 'section' => 'B', 'max_capacity' => 45],
            ['id' => 11, 'school_id' => 7, 'class_name' => 'Form 3A', 'grade_level' => '11', 'section' => 'A', 'max_capacity' => 45],
            ['id' => 12, 'school_id' => 7, 'class_name' => 'Form 3B', 'grade_level' => '11', 'section' => 'B', 'max_capacity' => 45],
            ['id' => 13, 'school_id' => 7, 'class_name' => 'Form 4A', 'grade_level' => '12', 'section' => 'A', 'max_capacity' => 45],
            ['id' => 14, 'school_id' => 7, 'class_name' => 'Form 4B', 'grade_level' => '12', 'section' => 'B', 'max_capacity' => 45],
        ];
        
        foreach ($classes as $class) {
            $existing = $db->table('school_classes')->where('id', $class['id'])->get()->getRow();
            if (!$existing) {
                $class['created_at'] = date('Y-m-d H:i:s');
                $db->table('school_classes')->insert($class);
            }
        }
        
        // Create students - 33 to 62 (30 students total)
        for ($i = 33; $i <= 62; $i++) {
            $existing = $db->table('users')->where('id', $i)->get()->getRow();
            if (!$existing) {
                $db->table('users')->insert([
                    'id' => $i,
                    'username' => "student{$i}",
                    'email' => "student{$i}@test.com",
                    'full_name' => "Student {$i}",
                    'password_hash' => password_hash('password', PASSWORD_DEFAULT),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
        
        // Create enrollments - 25 for school 6, 30 for school 7
        // School 6 (Nairobi Primary): students 33-57 (25 students)
        for ($i = 33; $i <= 57; $i++) {
            $existing = $db->table('student_enrollments')->where('student_id', $i)->get()->getRow();
            if (!$existing) {
                $db->table('student_enrollments')->insert([
                    'student_id' => $i,
                    'school_id' => 6,
                    'class_id' => (($i - 33) % 6) + 1, // Distribute across 6 classes
                    'status' => 'active',
                    'enrollment_date' => date('Y-m-d'),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
        
        // School 7 (Mombasa Secondary): students 33-62 (30 students)
        for ($i = 33; $i <= 62; $i++) {
            $existing = $db->table('student_enrollments')
                ->where('student_id', $i)
                ->where('school_id', 7)
                ->get()->getRow();
            if (!$existing) {
                $db->table('student_enrollments')->insert([
                    'student_id' => $i,
                    'school_id' => 7,
                    'class_id' => (($i - 33) % 8) + 7, // Distribute across 8 classes (ids 7-14)
                    'status' => 'active',
                    'enrollment_date' => date('Y-m-d'),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    public function testGetDashboardStats(): void
    {
        $stats = $this->service->getDashboardStats(6); // Nairobi Primary School

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('school', $stats);
        $this->assertArrayHasKey('total_classes', $stats);
        $this->assertArrayHasKey('total_students', $stats);
        $this->assertArrayHasKey('utilization_percent', $stats);
    }

    public function testGetDashboardStatsForSchool1(): void
    {
        $stats = $this->service->getDashboardStats(6); // Nairobi Primary

        $this->assertEquals(6, $stats['total_classes']); // Nairobi Primary has 6 classes
        $this->assertEquals(25, $stats['total_students']); // 25 students enrolled
        $this->assertGreaterThan(0, $stats['utilization_percent']);
    }

    public function testGetDashboardStatsForSchool2(): void
    {
        $stats = $this->service->getDashboardStats(7); // Mombasa Secondary

        $this->assertEquals(8, $stats['total_classes']); // Mombasa Secondary has 8 classes
        $this->assertEquals(30, $stats['total_students']); // 30 students enrolled
    }

    public function testGetSchoolOverview(): void
    {
        $overview = $this->service->getSchoolOverview(6); // Nairobi Primary

        $this->assertIsArray($overview);
        $this->assertArrayHasKey('classes', $overview);
        $this->assertIsArray($overview['classes']);
        $this->assertCount(6, $overview['classes']); // Nairobi Primary has 6 classes
    }

    public function testCanEnrollStudents(): void
    {
        $canEnroll = $this->service->canEnrollStudents(6, 1); // Nairobi Primary

        $this->assertIsBool($canEnroll);
    }

    public function testCanEnrollStudentsRespectsCap(): void
    {
        // School 6 (Nairobi Primary) max students is 500
        $canEnroll = $this->service->canEnrollStudents(6, 1000);

        $this->assertFalse($canEnroll); // Cannot enroll 1000 students
    }

    public function testGetAvailableSchools(): void
    {
        $schools = $this->service->getAvailableSchools();

        $this->assertIsArray($schools);
        $this->assertCount(5, $schools); // We have 5 schools
    }

    public function testGetDashboardStatsThrowsForInvalidSchool(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->service->getDashboardStats(999); // Non-existent school
    }

    public function testSchoolOverviewIncludesEnrollmentCounts(): void
    {
        $overview = $this->service->getSchoolOverview(6); // Nairobi Primary

        foreach ($overview['classes'] as $class) {
            $this->assertArrayHasKey('student_count', $class);
            $this->assertIsNumeric($class['student_count']);
        }
    }

    public function testUtilizationCalculation(): void
    {
        $stats = $this->service->getDashboardStats(6); // Nairobi Primary

        // Nairobi Primary: 25 students / 226 total capacity ≈ 11.06%
        $this->assertLessThanOrEqual(100, $stats['utilization_percent']);
        $this->assertGreaterThanOrEqual(0, $stats['utilization_percent']);
    }
}
