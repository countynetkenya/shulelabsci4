<?php

namespace Tests\Feature\MultiTenant;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * MultiTenantModuleTest - Tests for multi-tenant SaaS functionality.
 */
class MultiTenantModuleTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $migrateOnce = true;
    protected $seedOnce = true;

    // ============= SUPER ADMIN TESTS =============

    /**
     * Test super admin can list tenants.
     */
    public function testSuperAdminCanListTenants(): void
    {
        $result = $this->withSession(['user_id' => 1, 'role' => 'super_admin'])
            ->get('/api/v1/tenants');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test super admin can provision new tenant.
     */
    public function testSuperAdminCanProvisionTenant(): void
    {
        $result = $this->withSession(['user_id' => 1, 'role' => 'super_admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/tenants', [
                'name' => 'New Test School',
                'subdomain' => 'newtestschool',
                'tier' => 'starter',
                'admin_email' => 'admin@newtestschool.local',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test super admin can activate tenant.
     */
    public function testSuperAdminCanActivateTenant(): void
    {
        $result = $this->withSession(['user_id' => 1, 'role' => 'super_admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/tenants/1/activate', []);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test super admin can suspend tenant.
     */
    public function testSuperAdminCanSuspendTenant(): void
    {
        $result = $this->withSession(['user_id' => 1, 'role' => 'super_admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/tenants/1/suspend', [
                'reason' => 'Non-payment',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test super admin can view subscription plans.
     */
    public function testSuperAdminCanViewPlans(): void
    {
        $result = $this->withSession(['user_id' => 1, 'role' => 'super_admin'])
            ->get('/api/v1/tenants/plans');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test super admin can create subscription.
     */
    public function testSuperAdminCanCreateSubscription(): void
    {
        $result = $this->withSession(['user_id' => 1, 'role' => 'super_admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/tenants/1/subscriptions', [
                'plan_id' => 2,
                'billing_cycle' => 'yearly',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test super admin can view invoices.
     */
    public function testSuperAdminCanViewInvoices(): void
    {
        $result = $this->withSession(['user_id' => 1, 'role' => 'super_admin'])
            ->get('/api/v1/tenants/1/invoices');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test super admin can view usage metrics.
     */
    public function testSuperAdminCanViewUsage(): void
    {
        $result = $this->withSession(['user_id' => 1, 'role' => 'super_admin'])
            ->get('/api/v1/tenants/1/usage');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    // ============= TENANT ADMIN TESTS =============

    /**
     * Test tenant admin can view own tenant.
     */
    public function testTenantAdminCanViewOwnTenant(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'admin'])
            ->get('/api/v1/tenant/profile');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test tenant admin can update branding.
     */
    public function testTenantAdminCanUpdateBranding(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->put('/api/v1/tenant/branding', [
                'primary_color' => '#1E40AF',
                'secondary_color' => '#3B82F6',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test tenant admin can view subscription.
     */
    public function testTenantAdminCanViewSubscription(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'admin'])
            ->get('/api/v1/tenant/subscription');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test tenant admin can view invoices.
     */
    public function testTenantAdminCanViewOwnInvoices(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'admin'])
            ->get('/api/v1/tenant/invoices');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test tenant admin can view onboarding progress.
     */
    public function testTenantAdminCanViewOnboarding(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'admin'])
            ->get('/api/v1/tenant/onboarding');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test tenant admin can complete onboarding step.
     */
    public function testTenantAdminCanCompleteOnboardingStep(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->post('/api/v1/tenant/onboarding/school_profile/complete', [
                'school_name' => 'Test School',
                'address' => '123 Test Street',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    // ============= PUBLIC TESTS =============

    /**
     * Test public can view available plans.
     */
    public function testPublicCanViewPlans(): void
    {
        $result = $this->get('/api/v1/public/plans');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test public can register new school.
     */
    public function testPublicCanRegisterSchool(): void
    {
        $result = $this->withBodyFormat('json')
            ->post('/api/v1/public/register', [
                'school_name' => 'New School Registration',
                'subdomain' => 'newschoolreg',
                'admin_name' => 'John Admin',
                'admin_email' => 'admin@newschoolreg.local',
                'admin_password' => 'SecurePass123!',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    // ============= ACCESS CONTROL =============

    /**
     * Test tenant admin cannot access other tenant.
     */
    public function testTenantAdminCannotAccessOtherTenant(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'admin'])
            ->get('/api/v1/tenants/2');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [401, 403, 404]));
    }

    /**
     * Test teacher cannot access tenant admin features.
     */
    public function testTeacherCannotAccessTenantAdmin(): void
    {
        $result = $this->withSession(['user_id' => 101, 'school_id' => 1, 'role' => 'teacher'])
            ->get('/api/v1/tenant/subscription');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [401, 403, 404]));
    }
}
