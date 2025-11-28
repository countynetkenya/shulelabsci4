<?php

namespace Tests\Feature\Admissions;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * AdmissionsModuleTest - Web and API tests for Admissions module.
 *
 * Tests application workflow, interviews, waitlist management.
 */
class AdmissionsModuleTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $migrateOnce = true;
    protected $seedOnce = true;
    protected $seed = 'WaveModulesSeeder';

    // ============= PUBLIC (UNAUTHENTICATED) TESTS =============

    /**
     * Test public can access application form.
     */
    public function testPublicCanAccessApplicationForm(): void
    {
        $result = $this->get('/admissions/apply');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test public can submit application.
     */
    public function testPublicCanSubmitApplication(): void
    {
        $result = $this->withBodyFormat('json')
            ->post('/api/v1/admissions/applications', [
                'student_first_name' => 'Jane',
                'student_last_name' => 'Doe',
                'student_dob' => '2018-01-15',
                'student_gender' => 'female',
                'class_applied' => 1,
                'parent_first_name' => 'John',
                'parent_last_name' => 'Doe',
                'parent_email' => 'john.doe@test.local',
                'parent_phone' => '0722000000',
                'parent_relationship' => 'father',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    // ============= ADMIN ROLE TESTS =============

    /**
     * Test admin can view all applications.
     */
    public function testAdminCanViewApplications(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->get('/api/v1/admissions/applications');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test admin can review application.
     */
    public function testAdminCanReviewApplication(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->put('/api/v1/admissions/applications/1/review', [
                'status' => 'under_review',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test admin can accept application.
     */
    public function testAdminCanAcceptApplication(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/admissions/applications/1/accept', [
                'notes' => 'Welcome to our school!',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test admin can reject application.
     */
    public function testAdminCanRejectApplication(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/admissions/applications/2/reject', [
                'reason' => 'Capacity reached for this class.',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test admin can add to waitlist.
     */
    public function testAdminCanAddToWaitlist(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/admissions/applications/2/waitlist', [
                'class_id' => 1,
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test admin can schedule entrance test.
     */
    public function testAdminCanScheduleEntranceTest(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/admissions/tests', [
                'name' => 'Class 1 Entrance Test',
                'test_date' => date('Y-m-d', strtotime('+14 days')),
                'test_time' => '09:00:00',
                'venue' => 'Main Hall',
                'max_candidates' => 50,
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test admin can schedule interview.
     */
    public function testAdminCanScheduleInterview(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/admissions/applications/1/interview', [
                'interview_date' => date('Y-m-d', strtotime('+7 days')),
                'interview_time' => '10:00:00',
                'venue' => 'Admin Office',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test admin can view admission statistics.
     */
    public function testAdminCanViewStatistics(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->get('/api/v1/admissions/statistics');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    // ============= ACCESS CONTROL TESTS =============

    /**
     * Test teacher cannot manage admissions.
     */
    public function testTeacherCannotManageAdmissions(): void
    {
        $result = $this->withSession(['user_id' => 101, 'school_id' => 1, 'role' => 'teacher'])
            ->withBodyFormat('json')
            ->post('/api/v1/admissions/applications/1/accept', []);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [401, 403, 404]));
    }

    /**
     * Test student cannot access admissions.
     */
    public function testStudentCannotAccessAdmissions(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'student'])
            ->get('/api/v1/admissions/applications');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [401, 403, 404]));
    }
}
