<?php

namespace Tests\Feature\Analytics;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

/**
 * AnalyticsWebTest - Feature tests for Analytics CRUD.
 */
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

    public function testAdminCanViewAnalyticsIndex()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('analytics');

        $response->assertOK();
        $response->assertSee('Analytics Dashboards');
        $response->assertSee('fa-chart-bar');
    }

    public function testAdminCanViewCreateForm()
    {
        $response = $this->withSession($this->getAdminSession())
                         ->get('analytics/create');

        $response->assertOK();
        $response->assertSee('Create Dashboard');
        $response->assertSee('name');
        $response->assertSee('description');
    }

    public function testAdminCanCreateDashboard()
    {
        $data = [
            'name' => 'Test Dashboard',
            'description' => 'Test Description',
            'is_default' => 0,
            'is_shared' => 1,
        ];

        $response = $this->withSession($this->getAdminSession())
                         ->post('analytics/store', $data);

        $response->assertRedirectTo('/analytics');
        $response->assertSessionHas('message');
    }

    public function testCreateDashboardValidatesRequiredFields()
    {
        $data = [
            'description' => 'Missing name',
        ];

        $response = $this->withSession($this->getAdminSession())
                         ->post('analytics/store', $data);

        $response->assertRedirect();
        $response->assertSessionHas('errors');
    }

    public function testAdminCanViewEditForm()
    {
        // Create a dashboard first
        $db = \Config\Database::connect();
        $db->table('analytics_dashboards')->insert([
            'school_id' => 1,
            'name' => 'Edit Test Dashboard',
            'description' => 'Test Description',
            'layout' => json_encode([]),
            'is_default' => 0,
            'is_shared' => 0,
            'created_by' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $dashboardId = $db->insertID();

        $response = $this->withSession($this->getAdminSession())
                         ->get("analytics/edit/{$dashboardId}");

        $response->assertOK();
        $response->assertSee('Edit Dashboard');
        $response->assertSee('Edit Test Dashboard');
    }

    public function testAdminCanUpdateDashboard()
    {
        // Create a dashboard first
        $db = \Config\Database::connect();
        $db->table('analytics_dashboards')->insert([
            'school_id' => 1,
            'name' => 'Update Test Dashboard',
            'description' => 'Old Description',
            'layout' => json_encode([]),
            'is_default' => 0,
            'is_shared' => 0,
            'created_by' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $dashboardId = $db->insertID();

        $data = [
            'name' => 'Updated Dashboard Name',
            'description' => 'Updated Description',
            'is_default' => 1,
            'is_shared' => 1,
        ];

        $response = $this->withSession($this->getAdminSession())
                         ->post("analytics/update/{$dashboardId}", $data);

        $response->assertRedirectTo('/analytics');
        $response->assertSessionHas('message');
    }

    public function testAdminCanDeleteDashboard()
    {
        // Create a dashboard first
        $db = \Config\Database::connect();
        $db->table('analytics_dashboards')->insert([
            'school_id' => 1,
            'name' => 'Delete Test Dashboard',
            'description' => 'To be deleted',
            'layout' => json_encode([]),
            'is_default' => 0,
            'is_shared' => 0,
            'created_by' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $dashboardId = $db->insertID();

        $response = $this->withSession($this->getAdminSession())
                         ->get("analytics/delete/{$dashboardId}");

        $response->assertRedirectTo('/analytics');
        $response->assertSessionHas('message');
    }
}
