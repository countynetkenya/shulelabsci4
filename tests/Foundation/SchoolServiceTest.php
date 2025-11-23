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

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SchoolService();
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
