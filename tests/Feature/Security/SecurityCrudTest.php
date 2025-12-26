<?php

namespace Tests\Feature\Security;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

/**
 * SecurityCrudTest - Tests CRUD operations for Security module.
 */
class SecurityCrudTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use TenantTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
    }

    public function testIndexPageLoadsSuccessfully(): void
    {
        $result = $this->withSession($this->getAdminSession())
            ->get('security');

        $result->assertStatus(200);
        $result->assertSee('Security Access Logs');
    }

    public function testCreatePageLoadsSuccessfully(): void
    {
        $result = $this->withSession($this->getAdminSession())
            ->get('security/create');

        $result->assertStatus(200);
        $result->assertSee('Add Security Log Entry');
    }

    public function testCanCreateSecurityLog(): void
    {
        $data = [
            'identifier'     => 'test@example.com',
            'ip_address'     => '192.168.1.1',
            'attempt_type'   => 'login',
            'was_successful' => 1,
        ];

        $result = $this->withSession($this->getAdminSession())
            ->post('security', $data);

        $result->assertRedirectTo('/security');

        $this->seeInDatabase('login_attempts', [
            'identifier' => 'test@example.com',
        ]);
    }
}
