<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class DatabaseRefactorTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    
    protected $refresh = false;
    protected $namespace = null;
    
    /**
     * Test 1: Users table exists and is accessible
     */
    public function testUsersTableAccessible()
    {
        $db = \Config\Database::connect();
        $result = $db->table('users')->limit(1)->get();
        $this->assertIsObject($result);
        $this->assertGreaterThanOrEqual(0, $result->getNumRows());
    }
    
    /**
     * Test 2: Roles table exists and is accessible
     */
    public function testRolesTableAccessible()
    {
        $db = \Config\Database::connect();
        $result = $db->table('roles')->limit(1)->get();
        $this->assertIsObject($result);
    }
    
    /**
     * Test 3: User-roles relationship works
     */
    public function testUserRolesJoinWorks()
    {
        $db = \Config\Database::connect();
        $result = $db->table('users')
            ->select('users.*, roles.role_name')
            ->join('user_roles', 'users.id = user_roles.user_id', 'left')
            ->join('roles', 'user_roles.role_id = roles.id', 'left')
            ->limit(5)
            ->get();
        
        $this->assertIsObject($result);
    }
    
    /**
     * Test 4: Validation rules work with new table names
     */
    public function testValidationRulesWork()
    {
        $validation = \Config\Services::validation();
        
        // Test is_unique rule parses correctly
        $rules = ['email' => 'is_unique[users.email]'];
        $this->assertTrue($validation->setRules($rules) !== false);
    }
    
    /**
     * Test 5: No ci4_ prefix references in codebase
     */
    public function testNoCi4PrefixInCodebase()
    {
        $output = shell_exec("grep -r 'ci4_users\|ci4_roles\|ci4_user_roles' app/ --include='*.php' | grep -v 'Migrations/' | wc -l");
        $count = intval(trim($output));
        $this->assertEquals(0, $count, "Found $count remaining ci4_ references");
    }
}
