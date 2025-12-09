<?php

namespace Tests\Feature\MultiTenant;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

/**
 * MultiTenantCrudTest - Feature tests for MultiTenant CRUD operations
 * 
 * Tests all CRUD endpoints for the MultiTenant module.
 */
class MultiTenantCrudTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use TenantTestTrait;

    protected $migrate = true;
    protected $migrateOnce = false;
    protected $refresh = true;
    protected $namespace = 'App';

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
        
        // Override to use super admin (usertypeID = 0)
        $this->superAdminSession = [
            'logged_in'   => true,
            'user_id'     => 1,
            'school_id'   => $this->schoolId,
            'usertypeID'  => 0, // Super Admin
        ];
    }

    /**
     * Test: Index page displays tenants
     */
    public function testIndexDisplaysTenants()
    {
        // Seed a test tenant
        $this->db->table('tenants')->insert([
            'uuid'             => '550e8400-e29b-41d4-a716-446655440000',
            'name'             => 'Test School',
            'subdomain'        => 'test-school',
            'status'           => 'active',
            'tier'             => 'professional',
            'storage_quota_mb' => 5000,
            'storage_used_mb'  => 1000,
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->superAdminSession)
                       ->get('multitenant');

        $result->assertOK();
        $result->assertSee('Test School');
        $result->assertSee('test-school');
    }

    /**
     * Test: Create page displays form
     */
    public function testCreatePageDisplaysForm()
    {
        $result = $this->withSession($this->superAdminSession)
                       ->get('multitenant/create');

        $result->assertOK();
        $result->assertSee('Add New Tenant');
        $result->assertSee('Tenant Name');
        $result->assertSee('Subdomain');
    }

    /**
     * Test: Store creates a new tenant
     */
    public function testStoreCreatesTenant()
    {
        $result = $this->withSession($this->superAdminSession)
                       ->post('multitenant/store', [
                           'name'             => 'New School',
                           'subdomain'        => 'new-school',
                           'status'           => 'active',
                           'tier'             => 'starter',
                           'storage_quota_mb' => 5000,
                       ]);

        $result->assertRedirectTo('/multitenant');
        
        // Verify tenant was created
        $tenant = $this->db->table('tenants')
                           ->where('subdomain', 'new-school')
                           ->get()
                           ->getRowArray();
        
        $this->assertNotNull($tenant);
        $this->assertEquals('New School', $tenant['name']);
    }

    /**
     * Test: Edit page displays form with tenant data
     */
    public function testEditPageDisplaysFormWithData()
    {
        // Create a tenant first
        $tenantId = $this->db->table('tenants')->insert([
            'uuid'             => '550e8400-e29b-41d4-a716-446655440001',
            'name'             => 'Edit Test School',
            'subdomain'        => 'edit-test-school',
            'status'           => 'active',
            'tier'             => 'professional',
            'storage_quota_mb' => 5000,
            'storage_used_mb'  => 0,
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->superAdminSession)
                       ->get("multitenant/edit/{$tenantId}");

        $result->assertOK();
        $result->assertSee('Edit Tenant');
        $result->assertSee('Edit Test School');
        $result->assertSee('edit-test-school');
    }

    /**
     * Test: Update modifies an existing tenant
     */
    public function testUpdateModifiesTenant()
    {
        // Create a tenant first
        $tenantId = $this->db->table('tenants')->insert([
            'uuid'             => '550e8400-e29b-41d4-a716-446655440002',
            'name'             => 'Update Test School',
            'subdomain'        => 'update-test',
            'status'           => 'pending',
            'tier'             => 'free',
            'storage_quota_mb' => 2000,
            'storage_used_mb'  => 0,
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->superAdminSession)
                       ->post("multitenant/update/{$tenantId}", [
                           'name'             => 'Updated School Name',
                           'subdomain'        => 'update-test',
                           'status'           => 'active',
                           'tier'             => 'starter',
                           'storage_quota_mb' => 5000,
                       ]);

        $result->assertRedirectTo('/multitenant');
        
        // Verify tenant was updated
        $tenant = $this->db->table('tenants')
                           ->where('id', $tenantId)
                           ->get()
                           ->getRowArray();
        
        $this->assertEquals('Updated School Name', $tenant['name']);
        $this->assertEquals('active', $tenant['status']);
    }

    /**
     * Test: Delete removes a tenant
     */
    public function testDeleteRemovesTenant()
    {
        // Create a tenant first
        $tenantId = $this->db->table('tenants')->insert([
            'uuid'             => '550e8400-e29b-41d4-a716-446655440003',
            'name'             => 'Delete Test School',
            'subdomain'        => 'delete-test',
            'status'           => 'pending',
            'tier'             => 'free',
            'storage_quota_mb' => 2000,
            'storage_used_mb'  => 0,
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->superAdminSession)
                       ->get("multitenant/delete/{$tenantId}");

        $result->assertRedirectTo('/multitenant');
        
        // Verify tenant was deleted
        $tenant = $this->db->table('tenants')
                           ->where('id', $tenantId)
                           ->get()
                           ->getRowArray();
        
        $this->assertNull($tenant);
    }

    /**
     * Test: Non-super-admin users cannot access multitenant
     */
    public function testNonSuperAdminCannotAccessTenants()
    {
        // Use a regular admin session (usertypeID = 1)
        $session = [
            'logged_in'   => true,
            'user_id'     => 999,
            'school_id'   => $this->schoolId,
            'usertypeID'  => 1, // Regular admin, not super admin
        ];

        $result = $this->withSession($session)
                       ->get('multitenant');

        $result->assertRedirectTo('/login');
    }

    /**
     * Test: Activate tenant
     */
    public function testActivateTenant()
    {
        // Create a pending tenant
        $tenantId = $this->db->table('tenants')->insert([
            'uuid'             => '550e8400-e29b-41d4-a716-446655440004',
            'name'             => 'Pending School',
            'subdomain'        => 'pending-school',
            'status'           => 'pending',
            'tier'             => 'free',
            'storage_quota_mb' => 2000,
            'storage_used_mb'  => 0,
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->superAdminSession)
                       ->get("multitenant/activate/{$tenantId}");

        $result->assertRedirectTo('/multitenant');
        
        // Verify tenant was activated
        $tenant = $this->db->table('tenants')
                           ->where('id', $tenantId)
                           ->get()
                           ->getRowArray();
        
        $this->assertEquals('active', $tenant['status']);
        $this->assertNotNull($tenant['activated_at']);
    }
}
