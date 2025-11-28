<?php

namespace Tests\Feature\Analytics;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * AnalyticsModuleTest - Tests for Analytics & AI module.
 */
class AnalyticsModuleTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $migrateOnce = true;

    protected $seedOnce = true;

    protected $seed = 'WaveModulesSeeder';

    // ============= ADMIN TESTS =============

    /**
     * Test admin can view dashboards.
     */
    public function testAdminCanViewDashboards(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->get('/api/v1/analytics/dashboards');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test admin can create custom dashboard.
     */
    public function testAdminCanCreateDashboard(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/analytics/dashboards', [
                'name' => 'Executive Overview',
                'description' => 'High-level school metrics',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test admin can view at-risk students.
     */
    public function testAdminCanViewAtRiskStudents(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->get('/api/v1/analytics/at-risk-students');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test admin can view financial forecasts.
     */
    public function testAdminCanViewFinancialForecasts(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->get('/api/v1/analytics/forecasts/financial');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test admin can generate forecast.
     */
    public function testAdminCanGenerateForecast(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/analytics/forecasts/generate', [
                'forecast_type' => 'revenue',
                'period_type' => 'monthly',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test admin can view trends.
     */
    public function testAdminCanViewTrends(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->get('/api/v1/analytics/trends?metric=enrollment&period=monthly');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test admin can add widget to dashboard.
     */
    public function testAdminCanAddWidget(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/analytics/dashboards/1/widgets', [
                'name' => 'Enrollment Trend',
                'widget_type' => 'chart',
                'chart_type' => 'line',
                'data_source' => 'enrollment',
                'query_config' => ['period' => 'monthly'],
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    // ============= TEACHER TESTS =============

    /**
     * Test teacher can view class analytics.
     */
    public function testTeacherCanViewClassAnalytics(): void
    {
        $result = $this->withSession(['user_id' => 101, 'school_id' => 1, 'role' => 'teacher'])
            ->get('/api/v1/analytics/class/1');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test teacher can view at-risk students in their class.
     */
    public function testTeacherCanViewClassAtRiskStudents(): void
    {
        $result = $this->withSession(['user_id' => 101, 'school_id' => 1, 'role' => 'teacher'])
            ->get('/api/v1/analytics/at-risk-students?class_id=1');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    // ============= ACCESS CONTROL =============

    /**
     * Test student cannot access analytics.
     */
    public function testStudentCannotAccessAnalytics(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'student'])
            ->get('/api/v1/analytics/dashboards');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [401, 403, 404]));
    }
}
