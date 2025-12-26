<?php

namespace Tests\Feature\Integrations;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * IntegrationsCrudTest - Feature tests for Integrations module CRUD operations.
 *
 * @group integrations
 */
class IntegrationsCrudTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testIntegrationsIndexPageIsAccessible()
    {
        // Simulate admin session
        $session = [
            'loggedin' => true,
            'user_id' => 1,
            'usertypeID' => 1,
            'school_id' => 1,
        ];

        $result = $this->withSession($session)
            ->get('/integrations');

        $result->assertStatus(200);
        $result->assertSee('Integrations');
    }

    public function testIntegrationsCreatePageIsAccessible()
    {
        $session = [
            'loggedin' => true,
            'user_id' => 1,
            'usertypeID' => 1,
            'school_id' => 1,
        ];

        $result = $this->withSession($session)
            ->get('/integrations/create');

        $result->assertStatus(200);
        $result->assertSee('Create Integration');
    }

    public function testIntegrationsIndexRedirectsForNonAdminUsers()
    {
        $session = [
            'loggedin' => true,
            'user_id' => 2,
            'usertypeID' => 3, // Non-admin
            'school_id' => 1,
        ];

        $result = $this->withSession($session)
            ->get('/integrations');

        $result->assertRedirect();
    }
}
