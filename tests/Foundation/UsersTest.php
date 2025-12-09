<?php

namespace Tests\Foundation;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

class UsersTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $migrate = true;

    protected $migrateOnce = false;

    protected $refresh = true;

    protected $namespace = 'App';

    protected function setUp(): void
    {
        parent::setUp();

        // Disable CSRF for testing
        $config = config('Filters');
        // Handle different structure of globals['before']
        $newBefore = [];
        foreach ($config->globals['before'] as $key => $value) {
            if ($value !== 'csrf' && $key !== 'csrf') {
                if (is_array($value)) {
                    // If it's an array (like 'csrf' => ['except' => ...])
                    // we just skip it if the key is csrf
                    $newBefore[$key] = $value;
                } else {
                    $newBefore[] = $value;
                }
            }
        }
        $config->globals['before'] = $newBefore;
        \CodeIgniter\Config\Factories::injectMock('filters', 'Filters', $config);
    }

    public function testIndex()
    {
        $result = $this->call('get', '/system/users');
        $result->assertOK();
        $result->assertSee('Users Management');
    }

    public function testCreate()
    {
        $result = $this->call('get', '/system/users/create');
        $result->assertOK();
        $result->assertSee('Create New User');
    }

    public function testStore()
    {
        // Create a role first
        $this->db->table('roles')->insert([
            'role_name' => 'Test Role',
            'role_slug' => 'test-role',
            'description' => 'Test Description',
            'ci3_usertype_id' => 999,
        ]);
        $roleId = $this->db->insertID();

        $result = $this->call('post', '/system/users', [
            'full_name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role_id' => $roleId,
        ]);

        $result->assertRedirectTo('/system/users');
        $this->seeInDatabase('users', ['username' => 'testuser']);
        // We can't easily check user_roles without knowing the user ID, but we can check if a record exists
        // $this->seeInDatabase('user_roles', ['role_id' => $roleId]);
        // Actually seeInDatabase checks if ANY record matches.
        $this->seeInDatabase('user_roles', ['role_id' => $roleId]);
    }

    public function testEdit()
    {
        // Create user
        $this->db->table('users')->insert([
            'username' => 'edituser',
            'email' => 'edit@example.com',
            'password_hash' => 'hash',
            'full_name' => 'Edit User',
            'is_active' => 1,
        ]);
        $userId = $this->db->insertID();

        // Create role
        $this->db->table('roles')->insert([
            'role_name' => 'Edit Role',
            'role_slug' => 'edit-role',
            'description' => 'Desc',
            'ci3_usertype_id' => 999,
        ]);
        $roleId = $this->db->insertID();

        // Assign role
        $this->db->table('user_roles')->insert([
            'user_id' => $userId,
            'role_id' => $roleId,
        ]);

        $result = $this->call('get', "/system/users/edit/$userId");
        $result->assertOK();
        $result->assertSee('Edit User');
        $result->assertSee('edituser');
    }

    public function testUpdate()
    {
        // Create user
        $this->db->table('users')->insert([
            'username' => 'updateuser',
            'email' => 'update@example.com',
            'password_hash' => 'hash',
            'full_name' => 'Update User',
            'is_active' => 1,
        ]);
        $userId = $this->db->insertID();

        // Create role
        $this->db->table('roles')->insert([
            'role_name' => 'Update Role',
            'role_slug' => 'update-role',
            'description' => 'Desc',
            'ci3_usertype_id' => 999,
        ]);
        $roleId = $this->db->insertID();

        $result = $this->call('post', "/system/users/update/$userId", [
            'full_name' => 'Updated Name',
            'username' => 'updateuser',
            'email' => 'update@example.com',
            'role_id' => $roleId,
        ]);

        $result->assertRedirectTo('/system/users');
        $this->seeInDatabase('users', ['full_name' => 'Updated Name']);
    }

    public function testDelete()
    {
        // Create user
        $this->db->table('users')->insert([
            'username' => 'deleteuser',
            'email' => 'delete@example.com',
            'password_hash' => 'hash',
            'full_name' => 'Delete User',
            'is_active' => 1,
        ]);
        $userId = $this->db->insertID();

        $result = $this->call('get', "/system/users/delete/$userId");
        $result->assertRedirectTo('/system/users');
        $this->dontSeeInDatabase('users', ['id' => $userId]);
    }
}
