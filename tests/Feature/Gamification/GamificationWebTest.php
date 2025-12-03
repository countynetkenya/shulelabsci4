<?php

namespace Tests\Feature\Gamification;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

class GamificationWebTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use TenantTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
    }

    public function testAdminCanViewGamificationDashboard()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('gamification');

        $response->assertOK();
        $response->assertSee('Gamification Dashboard');
    }

    public function testApiLeaderboard()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('api/gamification/leaderboard');

        $response->assertOK();
        $response->assertJSONFragment(['top_students' => ['Alice', 'Bob', 'Charlie']]);
    }
}
