<?php

namespace Tests\Feature\LMS;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

class LMSDashboardTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use TenantTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
    }

    public function testCanViewLMSDashboard()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('lms');

        $response->assertOK();
        $response->assertSee('LMS Dashboard');
        $response->assertSee('Learning Management Features');
    }

    public function testDashboardShowsQuickActions()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('lms');

        $response->assertOK();
        $response->assertSee('Quick Actions');
        $response->assertSee('Manage Courses');
        $response->assertSee('Create New Course');
    }

    public function testDashboardShowsMetricCards()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('lms');

        $response->assertOK();
        $response->assertSee('Courses');
        $response->assertSee('Lessons');
        $response->assertSee('Enrollments');
        $response->assertSee('Completion');
    }

    public function testCanNavigateToCoursesFromDashboard()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('lms/courses');

        $response->assertOK();
    }
}
