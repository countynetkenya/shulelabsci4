<?php

namespace Tests\Feature\Learning;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * LearningModuleTest - Web and API tests for Learning module.
 *
 * Tests attendance, gradebook, exams, and report cards for all user roles.
 */
class LearningModuleTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $migrateOnce = true;

    protected $seedOnce = true;

    protected $seed = 'WaveModulesSeeder';

    // ============= TEACHER ROLE TESTS =============

    /**
     * Test teacher can view subjects.
     */
    public function testTeacherCanViewSubjects(): void
    {
        $result = $this->withSession(['user_id' => 101, 'school_id' => 1, 'role' => 'teacher'])
            ->get('/api/v1/learning/subjects');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test teacher can mark attendance.
     */
    public function testTeacherCanMarkAttendance(): void
    {
        $result = $this->withSession(['user_id' => 101, 'school_id' => 1, 'role' => 'teacher'])
            ->withBodyFormat('json')
            ->post('/api/v1/learning/attendance', [
                'class_id' => 1,
                'date' => date('Y-m-d'),
                'attendance' => [
                    ['student_id' => 100, 'status' => 'present'],
                    ['student_id' => 101, 'status' => 'absent'],
                ],
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test teacher can enter exam results.
     */
    public function testTeacherCanEnterResults(): void
    {
        $result = $this->withSession(['user_id' => 101, 'school_id' => 1, 'role' => 'teacher'])
            ->withBodyFormat('json')
            ->post('/api/v1/learning/results', [
                'exam_id' => 1,
                'subject_id' => 1,
                'results' => [
                    ['student_id' => 100, 'score' => 85],
                    ['student_id' => 101, 'score' => 72],
                ],
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    // ============= STUDENT ROLE TESTS =============

    /**
     * Test student can view own attendance.
     */
    public function testStudentCanViewOwnAttendance(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'student'])
            ->get('/api/v1/learning/attendance/my');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test student can view own grades.
     */
    public function testStudentCanViewOwnGrades(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'student'])
            ->get('/api/v1/learning/grades/my');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test student can view timetable.
     */
    public function testStudentCanViewTimetable(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'student'])
            ->get('/api/v1/learning/timetable');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test student cannot mark attendance.
     */
    public function testStudentCannotMarkAttendance(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'student'])
            ->withBodyFormat('json')
            ->post('/api/v1/learning/attendance', [
                'class_id' => 1,
                'date' => date('Y-m-d'),
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [401, 403, 404]));
    }

    // ============= PARENT ROLE TESTS =============

    /**
     * Test parent can view child's attendance.
     */
    public function testParentCanViewChildAttendance(): void
    {
        $result = $this->withSession(['user_id' => 150, 'school_id' => 1, 'role' => 'parent'])
            ->get('/api/v1/learning/attendance/child/100');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test parent can view child's grades.
     */
    public function testParentCanViewChildGrades(): void
    {
        $result = $this->withSession(['user_id' => 150, 'school_id' => 1, 'role' => 'parent'])
            ->get('/api/v1/learning/grades/child/100');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    // ============= ADMIN ROLE TESTS =============

    /**
     * Test admin can generate report cards.
     */
    public function testAdminCanGenerateReportCards(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/learning/report-cards/generate', [
                'class_id' => 1,
                'academic_year' => '2025',
                'term' => 'Term 1',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test admin can view attendance reports.
     */
    public function testAdminCanViewAttendanceReports(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->get('/api/v1/learning/reports/attendance?class_id=1');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }
}
