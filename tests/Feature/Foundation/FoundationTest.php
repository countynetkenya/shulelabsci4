<?php

namespace Tests\Feature\Foundation;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

/**
 * FoundationTest - Feature tests for Foundation Settings.
 *
 * Tests the Foundation module's settings management functionality.
 */
class FoundationTest extends CIUnitTestCase
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
     * Test: Settings index page loads.
     */
    public function testSettingsIndexPageLoads()
    {
        // Seed some test settings
        $this->db->table('settings')->insertBatch([
            [
                'class'      => 'general',
                'key'        => 'platform_name',
                'value'      => 'ShuleLabs',
                'type'       => 'string',
                'context'    => 'system',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'class'      => 'general',
                'key'        => 'support_email',
                'value'      => 'support@shulelabs.com',
                'type'       => 'string',
                'context'    => 'system',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ]);

        $result = $this->withSession($this->getAdminSession())
                       ->get('system/settings');

        $result->assertOK();
        $result->assertSee('Settings');
    }

    /**
     * Test: Settings can be updated.
     */
    public function testSettingsCanBeUpdated()
    {
        // Seed initial settings
        $this->db->table('settings')->insert([
            'class'      => 'general',
            'key'        => 'platform_name',
            'value'      => 'OldName',
            'type'       => 'string',
            'context'    => 'system',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())
                       ->post('system/settings', [
                           'platform_name' => 'NewPlatformName',
                           'support_email' => 'newsupport@example.com',
                       ]);

        $result->assertRedirect();

        // Verify settings were updated
        $setting = $this->db->table('settings')
                            ->where('class', 'general')
                            ->where('key', 'platform_name')
                            ->get()
                            ->getRowArray();

        $this->assertEquals('NewPlatformName', $setting['value']);
    }

    /**
     * Test: Mail settings can be updated.
     */
    public function testMailSettingsCanBeUpdated()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->post('system/settings', [
                           'mail_host'     => 'smtp.gmail.com',
                           'mail_port'     => '587',
                           'mail_username' => 'test@gmail.com',
                           'mail_password' => 'password123',
                       ]);

        $result->assertRedirect();

        // Verify mail host was created/updated
        $setting = $this->db->table('settings')
                            ->where('class', 'mail')
                            ->where('key', 'host')
                            ->get()
                            ->getRowArray();

        $this->assertNotNull($setting);
        $this->assertEquals('smtp.gmail.com', $setting['value']);
    }

    /**
     * Test: Payment settings can be updated.
     */
    public function testPaymentSettingsCanBeUpdated()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->post('system/settings', [
                           'pesapal_key'    => 'test_key_123',
                           'pesapal_secret' => 'test_secret_456',
                       ]);

        $result->assertRedirect();

        // Verify pesapal key was created/updated
        $setting = $this->db->table('settings')
                            ->where('class', 'payment')
                            ->where('key', 'pesapal_key')
                            ->get()
                            ->getRowArray();

        $this->assertNotNull($setting);
        $this->assertEquals('test_key_123', $setting['value']);
    }

    /**
     * Test: Roles index page loads.
     */
    public function testRolesIndexPageLoads()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('system/roles');

        $result->assertOK();
        $result->assertSee('Roles');
    }

    /**
     * Test: Users index page loads.
     */
    public function testUsersIndexPageLoads()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('system/users');

        $result->assertOK();
        $result->assertSee('Users');
    }

    /**
     * Test: Tenants index page loads (Foundation tenant management).
     */
    public function testTenantsIndexPageLoads()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('system/tenants');

        $result->assertOK();
        $result->assertSee('Tenants');
    }

    /**
     * Test: Operations dashboard loads.
     */
    public function testOperationsDashboardLoads()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('operations/dashboard');

        $result->assertOK();
        $result->assertSee('Operations Dashboard');
    }

    /**
     * Test: Health check endpoint works.
     */
    public function testHealthCheckWorks()
    {
        $result = $this->get('system/health');

        $result->assertOK();
    }
}
