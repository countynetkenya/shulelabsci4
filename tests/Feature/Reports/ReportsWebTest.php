<?php

namespace Tests\Feature\Reports;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

class ReportsWebTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use TenantTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
    }

    public function testAdminCanViewReportsDashboard()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('reports');

        $response->assertOK();
        $response->assertSee('Reports Dashboard');
    }
}
