<?php

namespace Tests\Feature\Analytics;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

class AnalyticsWebTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use TenantTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
    }

    public function testAdminCanViewAnalyticsDashboard()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('analytics');

        $response->assertOK();
        $response->assertSee('Analytics Dashboard');
    }

    public function testApiSummary()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('api/analytics/summary');

        $response->assertOK();
        $response->assertJSONFragment(['students' => 1200]);
    }
}
