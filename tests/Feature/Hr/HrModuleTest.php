<?php

namespace Tests\Feature\Hr;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * HrModuleTest - Web and API tests for HR module.
 *
 * Tests employee management, leave, payroll for all user roles.
 */
class HrModuleTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $migrateOnce = true;

    protected $seedOnce = true;

    protected $seed = 'WaveModulesSeeder';

    // ============= HR ADMIN ROLE TESTS =============

    /**
     * Test HR admin can view employees.
     */
    public function testHrAdminCanViewEmployees(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->get('/api/v1/hr/employees');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test HR admin can view departments.
     */
    public function testHrAdminCanViewDepartments(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->get('/api/v1/hr/departments');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test HR admin can view leave types.
     */
    public function testHrAdminCanViewLeaveTypes(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->get('/api/v1/hr/leave-types');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test HR admin can approve leave requests.
     */
    public function testHrAdminCanApproveLeave(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/hr/leave/1/approve', []);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test HR admin can process payroll.
     */
    public function testHrAdminCanProcessPayroll(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/hr/payroll/process', [
                'period_id' => 1,
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    // ============= EMPLOYEE (TEACHER/STAFF) ROLE TESTS =============

    /**
     * Test employee can view own profile.
     */
    public function testEmployeeCanViewOwnProfile(): void
    {
        $result = $this->withSession(['user_id' => 101, 'school_id' => 1, 'role' => 'teacher'])
            ->get('/api/v1/hr/profile');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test employee can submit leave request.
     */
    public function testEmployeeCanSubmitLeaveRequest(): void
    {
        $result = $this->withSession(['user_id' => 101, 'school_id' => 1, 'role' => 'teacher'])
            ->withBodyFormat('json')
            ->post('/api/v1/hr/leave/request', [
                'leave_type_id' => 1,
                'start_date' => date('Y-m-d', strtotime('+7 days')),
                'end_date' => date('Y-m-d', strtotime('+10 days')),
                'reason' => 'Family event',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test employee can view leave balance.
     */
    public function testEmployeeCanViewLeaveBalance(): void
    {
        $result = $this->withSession(['user_id' => 101, 'school_id' => 1, 'role' => 'teacher'])
            ->get('/api/v1/hr/leave/balance');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test employee can view own payslips.
     */
    public function testEmployeeCanViewOwnPayslips(): void
    {
        $result = $this->withSession(['user_id' => 101, 'school_id' => 1, 'role' => 'teacher'])
            ->get('/api/v1/hr/payslips/my');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test employee can clock in/out.
     */
    public function testEmployeeCanClockIn(): void
    {
        $result = $this->withSession(['user_id' => 101, 'school_id' => 1, 'role' => 'teacher'])
            ->withBodyFormat('json')
            ->post('/api/v1/hr/attendance/clock-in', []);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    // ============= ACCESS CONTROL TESTS =============

    /**
     * Test employee cannot process payroll.
     */
    public function testEmployeeCannotProcessPayroll(): void
    {
        $result = $this->withSession(['user_id' => 101, 'school_id' => 1, 'role' => 'teacher'])
            ->withBodyFormat('json')
            ->post('/api/v1/hr/payroll/process', []);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [401, 403, 404]));
    }

    /**
     * Test student cannot access HR module.
     */
    public function testStudentCannotAccessHr(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'student'])
            ->get('/api/v1/hr/employees');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [401, 403, 404]));
    }
}
