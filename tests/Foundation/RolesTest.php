<?php

namespace Tests\Foundation;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Modules\Foundation\Services\RolesService;

class RolesTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;

    protected $migrateOnce = false;

    protected $refresh = true;

    protected $namespace = 'App';

    protected function setUp(): void
    {
        parent::setUp();

        // Disable CSRF for testing
        $config = config('Filters');

        // Remove CSRF if it's a key
        if (isset($config->globals['before']['csrf'])) {
            unset($config->globals['before']['csrf']);
        }

        // Remove CSRF if it's a value
        $key = array_search('csrf', $config->globals['before']);
        if ($key !== false) {
            unset($config->globals['before'][$key]);
        }

        \CodeIgniter\Config\Factories::injectMock('filters', 'filters', $config);
    }

    public function testCreateRole()
    {
        $service = new RolesService();
        $data = [
            'role_name' => 'Test Role',
            'role_slug' => 'test-role',
            'description' => 'A test role',
            'ci3_usertype_id' => 999,
        ];

        $id = $service->createRole($data);
        $this->assertIsInt($id);

        $this->seeInDatabase('roles', ['role_slug' => 'test-role']);
    }

    public function testRoleControllerIndex()
    {
        $result = $this->get('system/roles');
        $result->assertOK();
        $result->assertSee('System Roles');
    }

    public function testRoleControllerCreate()
    {
        $result = $this->call('post', 'system/roles', [
            'role_name' => 'Controller Role',
            'role_slug' => 'controller-role',
            'description' => 'Created via controller',
        ]);

        $result->assertRedirectTo('/system/roles');
        $this->seeInDatabase('roles', ['role_slug' => 'controller-role']);
    }
}
