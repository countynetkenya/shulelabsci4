<?php

namespace Tests\Feature\LMS;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

class LMSWebTest extends CIUnitTestCase
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
            ->get('/lms');

        $result->assertOK();
        $result->assertSee('LMS Module');
    }

    public function testApiCoursesEndpoint()
    {
        $result = $this->withHeaders([
            'X-Tenant-ID' => '1',
            'X-Actor-ID' => '1',
        ])->get('/api/lms/courses');

        // We expect it to reach the controller.
        $this->assertNotEquals(404, $result->response()->getStatusCode());
    }
}
