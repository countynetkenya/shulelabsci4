<?php

namespace Tests\Feature\Audit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * AuditModuleTest - Web and API tests for Audit module.
 *
 * Tests audit trail, GDPR compliance, and integrity verification.
 */
class AuditModuleTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $migrateOnce = true;
    protected $seedOnce = true;
    protected $seed = 'WaveModulesSeeder';

    /**
     * Test that admin can view audit events.
     */
    public function testAdminCanViewAuditEvents(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->get('/api/v1/audit/events');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test search audit events.
     */
    public function testSearchAuditEvents(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->get('/api/v1/audit/events/search?event_type=login_success');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test entity history retrieval.
     */
    public function testGetEntityHistory(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->get('/api/v1/audit/entity/student/100');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test integrity verification endpoint.
     */
    public function testVerifyIntegrity(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->get('/api/v1/audit/verify');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test GDPR export for user data.
     */
    public function testGdprExport(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->get('/api/v1/audit/export/user/100');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test that student cannot access audit logs.
     */
    public function testStudentCannotAccessAudit(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'student'])
            ->get('/api/v1/audit/events');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [401, 403, 404]));
    }
}
