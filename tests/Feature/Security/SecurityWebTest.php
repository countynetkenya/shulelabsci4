<?php

namespace Tests\Feature\Security;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

class SecurityWebTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use TenantTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
    }

    public function testAdminCanViewSecurityDashboard()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('security');

        $response->assertOK();
        $response->assertSee('Security Dashboard');
    }

    public function testApiRoles()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('api/security/roles');

        $response->assertOK();
        $response->assertJSONFragment(['roles' => ['Admin', 'Teacher', 'Student']]);
    }
}
