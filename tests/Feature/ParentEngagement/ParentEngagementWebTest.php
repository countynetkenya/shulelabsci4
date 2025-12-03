<?php

namespace Tests\Feature\ParentEngagement;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

class ParentEngagementWebTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use TenantTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
    }

    public function testAdminCanViewParentEngagementDashboard()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('parent-engagement');

        $response->assertOK();
        $response->assertSee('Parent Engagement Dashboard');
    }

    public function testApiMessages()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('api/parent-engagement/messages');

        $response->assertOK();
        $response->assertSee('Hello');
    }
}
