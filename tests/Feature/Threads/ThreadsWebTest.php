<?php

namespace Tests\Feature\Threads;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

class ThreadsWebTest extends CIUnitTestCase
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
            ->get('/threads');

        $result->assertOK();
        $result->assertSee('Threads Module');
    }

    public function testApiEndpoint()
    {
        $result = $this->withHeaders([
            'X-Tenant-ID' => '1',
            'X-Actor-ID' => '1',
        ])->get('/api/threads');

        $this->assertNotEquals(404, $result->response()->getStatusCode());
    }
}
