<?php

namespace Tests\Feature\Hr;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

class HrWebTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use TenantTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
    }

    public function testAdminCanViewDashboard()
    {
        $result = $this->withSession($this->getAdminSession())
            ->get('/hr');

        $result->assertOK();
        $result->assertSee('HR Module');
    }

    public function testApiPendingApprovals()
    {
        $result = $this->withHeaders([
            'X-Tenant-ID' => '1',
            'X-Actor-ID' => '1',
        ])->get('/api/hr/payroll/approvals/pending');

        // We expect it to reach the controller.
        $this->assertNotEquals(404, $result->response()->getStatusCode());
    }
}
