<?php

namespace Tests\Feature\Analytics;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @group Analytics
 */
class AnalyticsCrudTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App\Modules\Analytics';

    protected $migrate = true;

    protected $migrateOnce = false;

    protected $refresh = true;

    protected $seed = '';

    protected function setUp(): void
    {
        parent::setUp();

        // Mock session for authenticated admin user
        $_SESSION['school_id'] = 1;
        $_SESSION['schoolID'] = 1;
        $_SESSION['user_id'] = 1;
        $_SESSION['loginuserID'] = 1;
        $_SESSION['usertypeID'] = 1;
        $_SESSION['is_admin'] = true;
    }

    public function testAnalyticsIndexPageLoads()
    {
        $result = $this->withSession($_SESSION)
            ->get('analytics');

        $result->assertStatus(200);
        $result->assertSee('Analytics Dashboard');
    }

    public function testAnalyticsCreatePageLoads()
    {
        $result = $this->withSession($_SESSION)
            ->get('analytics/create');

        $result->assertStatus(200);
        $result->assertSee('Create Dashboard');
    }

    public function testCanCreateAnalyticsDashboard()
    {
        $data = [
            'name' => 'Test Dashboard',
            'description' => 'Test dashboard for analytics',
            'is_default' => 1,
            'is_shared' => 1,
        ];

        $result = $this->withSession($_SESSION)
            ->post('analytics/store', $data);

        $result->assertRedirectTo('/analytics');

        // Verify data was inserted
        $this->seeInDatabase('analytics_dashboards', [
            'name' => 'Test Dashboard',
            'school_id' => 1,
        ]);
    }

    public function testAnalyticsEditPageLoads()
    {
        // Insert a test dashboard
        $dashboardId = $this->insertTestDashboard();

        $result = $this->withSession($_SESSION)
            ->get("analytics/edit/{$dashboardId}");

        $result->assertStatus(200);
        $result->assertSee('Edit Dashboard');
    }

    public function testCanUpdateAnalyticsDashboard()
    {
        $dashboardId = $this->insertTestDashboard();

        $data = [
            'name' => 'Updated Dashboard',
            'description' => 'Updated description',
            'is_default' => 0,
            'is_shared' => 0,
        ];

        $result = $this->withSession($_SESSION)
            ->post("analytics/update/{$dashboardId}", $data);

        $result->assertRedirectTo('/analytics');

        $this->seeInDatabase('analytics_dashboards', [
            'id' => $dashboardId,
            'name' => 'Updated Dashboard',
        ]);
    }

    public function testCanDeleteAnalyticsDashboard()
    {
        $dashboardId = $this->insertTestDashboard();

        $result = $this->withSession($_SESSION)
            ->get("analytics/delete/{$dashboardId}");

        $result->assertRedirectTo('/analytics');

        $this->dontSeeInDatabase('analytics_dashboards', [
            'id' => $dashboardId,
        ]);
    }

    protected function insertTestDashboard(): int
    {
        $db = \Config\Database::connect();
        $data = [
            'school_id' => 1,
            'name' => 'Test Dashboard',
            'description' => 'Test description',
            'layout' => '{}',
            'is_default' => 0,
            'is_shared' => 0,
            'created_by' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $db->table('analytics_dashboards')->insert($data);
        return (int) $db->insertID();
    }
}
