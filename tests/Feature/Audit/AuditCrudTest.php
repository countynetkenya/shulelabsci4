<?php

namespace Tests\Feature\Audit;

use Tests\Support\TestCase;

/**
 * AuditCrudTest - Feature tests for Audit module CRUD operations
 * 
 * @group audit
 */
class AuditCrudTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testAuditIndexPageIsAccessible()
    {
        // Simulate admin session
        $session = [
            'loggedin' => true,
            'user_id' => 1,
            'usertypeID' => 1,
            'school_id' => 1,
        ];

        $result = $this->withSession($session)
            ->get('/audit');

        $result->assertStatus(200);
        $result->assertSee('Audit Logs');
    }

    public function testAuditCreatePageIsAccessible()
    {
        $session = [
            'loggedin' => true,
            'user_id' => 1,
            'usertypeID' => 1,
            'school_id' => 1,
        ];

        $result = $this->withSession($session)
            ->get('/audit/create');

        $result->assertStatus(200);
        $result->assertSee('Create Manual Audit Entry');
    }

    public function testAuditIndexRedirectsForNonAdminUsers()
    {
        $session = [
            'loggedin' => true,
            'user_id' => 2,
            'usertypeID' => 3, // Non-admin
            'school_id' => 1,
        ];

        $result = $this->withSession($session)
            ->get('/audit');

        $result->assertRedirect();
    }
}
