<?php

namespace Tests\Feature\Scheduler;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

class SchedulerWebTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use TenantTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
    }

    public function testAdminCanViewSchedulerDashboard()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('scheduler');

        $response->assertOK();
        $response->assertSee('Scheduler Dashboard');
    }

    /*
    public function testApiJobs()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('api/scheduler/jobs');

        $response->assertOK();
    }
    */
}
