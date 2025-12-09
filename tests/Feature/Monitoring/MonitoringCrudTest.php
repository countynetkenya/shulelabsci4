<?php

namespace Tests\Feature\Monitoring;

use Tests\Support\TestCase;

/**
 * MonitoringCrudTest - Feature tests for Monitoring module CRUD operations
 * 
 * @group monitoring
 */
class MonitoringCrudTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testMonitoringIndexPageIsAccessible()
    {
        // Simulate admin session
        $session = [
            'loggedin' => true,
            'user_id' => 1,
            'usertypeID' => 1,
            'school_id' => 1,
        ];

        $result = $this->withSession($session)
            ->get('/monitoring');

        $result->assertStatus(200);
        $result->assertSee('System Monitoring');
    }

    public function testMonitoringCreatePageIsAccessible()
    {
        $session = [
            'loggedin' => true,
            'user_id' => 1,
            'usertypeID' => 1,
            'school_id' => 1,
        ];

        $result = $this->withSession($session)
            ->get('/monitoring/create');

        $result->assertStatus(200);
        $result->assertSee('Create Metric');
    }

    public function testMonitoringIndexRedirectsForNonAdminUsers()
    {
        $session = [
            'loggedin' => true,
            'user_id' => 2,
            'usertypeID' => 3, // Non-admin
            'school_id' => 1,
        ];

        $result = $this->withSession($session)
            ->get('/monitoring');

        $result->assertRedirect();
    }
}
