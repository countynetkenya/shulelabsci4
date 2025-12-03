<?php

namespace Tests\Feature\Transport;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

class TransportWebTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use TenantTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
    }

    public function testAdminCanViewTransportDashboard()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('transport');

        $response->assertOK();
        $response->assertSee('Transport Management');
    }

    public function testApiRoutes()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('api/transport/routes');

        $response->assertOK();
        $response->assertSee('Route A');
    }
}
