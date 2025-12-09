<?php

namespace Tests\Feature\Admin;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

/**
 * AdminCrudTest - Feature tests for Admin Settings CRUD operations
 * 
 * Tests all CRUD endpoints for the Admin Settings module:
 * - GET /admin/settings (index)
 * - GET /admin/settings/create (create form)
 * - POST /admin/settings/store (create action)
 * - GET /admin/settings/edit/{id} (edit form)
 * - POST /admin/settings/update/{id} (update action)
 * - GET /admin/settings/delete/{id} (delete action)
 */
class AdminCrudTest extends CIUnitTestCase
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
    }

    /**
     * Test: Index page displays settings
     */
    public function testIndexDisplaysSettings()
    {
        // Seed a test setting
        $this->db->table('settings')->insert([
            'class'      => 'general',
            'key'        => 'app_name',
            'value'      => 'ShuleLabs',
            'type'       => 'string',
            'context'    => 'app',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())
                       ->get('admin/settings');

        $result->assertOK();
        $result->assertSee('general');
        $result->assertSee('app_name');
        $result->assertSee('ShuleLabs');
    }

    /**
     * Test: Index page shows empty state when no settings
     */
    public function testIndexShowsEmptyState()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('admin/settings');

        $result->assertOK();
        $result->assertSee('No settings found');
    }

    /**
     * Test: Create page displays form
     */
    public function testCreatePageDisplaysForm()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('admin/settings/create');

        $result->assertOK();
        $result->assertSee('Add New Setting');
        $result->assertSee('Category (Class)');
        $result->assertSee('Key');
    }

    /**
     * Test: Store creates a new setting
     */
    public function testStoreCreatesSetting()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->post('admin/settings/store', [
                           'class'   => 'mail',
                           'key'     => 'smtp_host',
                           'value'   => 'smtp.example.com',
                           'type'    => 'string',
                           'context' => 'system',
                       ]);

        $result->assertRedirectTo('/admin/settings');
        
        // Verify setting was created
        $setting = $this->db->table('settings')
                            ->where('class', 'mail')
                            ->where('key', 'smtp_host')
                            ->get()
                            ->getRowArray();
        
        $this->assertNotNull($setting);
        $this->assertEquals('smtp.example.com', $setting['value']);
    }

    /**
     * Test: Store validation fails with missing class
     */
    public function testStoreValidationFailsWithMissingClass()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->post('admin/settings/store', [
                           'key'     => 'some_key',
                           'value'   => 'some_value',
                       ]);

        $result->assertRedirect();
        $result->assertSessionHas('errors');
    }

    /**
     * Test: Edit page displays form with setting data
     */
    public function testEditPageDisplaysFormWithData()
    {
        // Create a setting first
        $settingId = $this->db->table('settings')->insert([
            'class'      => 'payment',
            'key'        => 'currency',
            'value'      => 'USD',
            'type'       => 'string',
            'context'    => 'app',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())
                       ->get("admin/settings/edit/{$settingId}");

        $result->assertOK();
        $result->assertSee('Edit Setting');
        $result->assertSee('payment');
        $result->assertSee('currency');
        $result->assertSee('USD');
    }

    /**
     * Test: Update modifies an existing setting
     */
    public function testUpdateModifiesSetting()
    {
        // Create a setting first
        $settingId = $this->db->table('settings')->insert([
            'class'      => 'general',
            'key'        => 'timezone',
            'value'      => 'UTC',
            'type'       => 'string',
            'context'    => 'app',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())
                       ->post("admin/settings/update/{$settingId}", [
                           'class'   => 'general',
                           'key'     => 'timezone',
                           'value'   => 'Africa/Nairobi',
                           'type'    => 'string',
                           'context' => 'app',
                       ]);

        $result->assertRedirectTo('/admin/settings');
        
        // Verify setting was updated
        $setting = $this->db->table('settings')
                            ->where('id', $settingId)
                            ->get()
                            ->getRowArray();
        
        $this->assertEquals('Africa/Nairobi', $setting['value']);
    }

    /**
     * Test: Delete removes a setting
     */
    public function testDeleteRemovesSetting()
    {
        // Create a setting first
        $settingId = $this->db->table('settings')->insert([
            'class'      => 'temp',
            'key'        => 'temp_setting',
            'value'      => 'temp_value',
            'type'       => 'string',
            'context'    => 'app',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())
                       ->get("admin/settings/delete/{$settingId}");

        $result->assertRedirectTo('/admin/settings');
        
        // Verify setting was deleted
        $setting = $this->db->table('settings')
                            ->where('id', $settingId)
                            ->get()
                            ->getRowArray();
        
        $this->assertNull($setting);
    }

    /**
     * Test: Non-admin users cannot access admin settings
     */
    public function testNonAdminCannotAccessSettings()
    {
        // Use a non-admin session (usertypeID = 2)
        $session = [
            'logged_in'   => true,
            'user_id'     => 999,
            'school_id'   => $this->schoolId,
            'usertypeID'  => 2, // Non-admin
        ];

        $result = $this->withSession($session)
                       ->get('admin/settings');

        $result->assertRedirectTo('/login');
    }

    /**
     * Test: Filter settings by class
     */
    public function testFilterByClass()
    {
        // Seed multiple settings with different classes
        $this->db->table('settings')->insertBatch([
            [
                'class'      => 'mail',
                'key'        => 'smtp_host',
                'value'      => 'smtp.example.com',
                'type'       => 'string',
                'context'    => 'system',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'class'      => 'payment',
                'key'        => 'currency',
                'value'      => 'KES',
                'type'       => 'string',
                'context'    => 'app',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ]);

        $result = $this->withSession($this->getAdminSession())
                       ->get('admin/settings?class=mail');

        $result->assertOK();
        $result->assertSee('smtp_host');
        $result->assertDontSee('currency');
    }
}
