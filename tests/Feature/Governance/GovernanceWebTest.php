<?php

namespace Tests\Feature\Governance;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

class GovernanceWebTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use TenantTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
    }

    public function testAdminCanViewGovernanceDashboard()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('governance');

        $response->assertOK();
        $response->assertSee('Governance Dashboard');
    }

    public function testApiPolicies()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('api/governance/policies');

        $response->assertOK();
        $response->assertJSONFragment(['policies' => ['Policy A', 'Policy B']]);
    }
}
