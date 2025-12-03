<?php

namespace Tests\Feature\Mobile;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

class MobileWebTest extends CIUnitTestCase
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
            ->get('/mobile');

        $result->assertOK();
        $result->assertSee('Mobile Module');
    }

    public function testApiTelemetryEndpoint()
    {
        $result = $this->withHeaders([
            'X-Tenant-ID' => '1',
            'X-Actor-ID' => '1',
        ])->get('/api/mobile/telemetry/snapshots');

        // We expect it to reach the controller.
        $this->assertNotEquals(404, $result->response()->getStatusCode());
    }
}
