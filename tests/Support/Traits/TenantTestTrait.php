<?php

namespace Tests\Support\Traits;

/**
 * TenantTestTrait - Provides standardized tenant context for feature tests
 * 
 * This trait sets up a complete tenant context including:
 * - School record
 * - Admin user with role
 * - Session state
 * 
 * Usage:
 * ```php
 * class MyTest extends CIUnitTestCase
 * {
 *     use TenantTestTrait;
 *     
 *     protected function setUp(): void
 *     {
 *         parent::setUp();
 *         $this->setupTenantContext();
 *     }
 *     
 *     public function testSomething()
 *     {
 *         $this->withSession($this->getAdminSession())
 *              ->get('/some-route');
 *     }
 * }
 * ```
 */
trait TenantTestTrait
{
    protected int $schoolId;
    protected int $userId;
    protected int $roleId;

    /**
     * Set up complete tenant context for testing
     */
    protected function setupTenantContext(): void
    {
        // Create a test school
        $this->db->table('schools')->insert([
            'school_name' => 'Test School',
            'address'     => '123 Test Street',
            'phone'       => '1234567890',
            'email'       => 'test@school.com',
            'is_active'   => 1,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        $this->schoolId = (int) $this->db->insertID();

        // Create admin role
        $this->db->table('roles')->insert([
            'name'        => 'Administrator',
            'description' => 'System Administrator',
            'is_active'   => 1,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        $this->roleId = (int) $this->db->insertID();

        // Create test admin user
        $this->db->table('users')->insert([
            'username'      => 'testadmin',
            'email'         => 'admin@test.com',
            'password_hash' => password_hash('password', PASSWORD_DEFAULT),
            'full_name'     => 'Test Admin',
            'schoolID'      => (string) $this->schoolId,
            'is_active'     => 1,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
        $this->userId = (int) $this->db->insertID();

        // Assign role to user
        $this->db->table('user_roles')->insert([
            'user_id' => $this->userId,
            'role_id' => $this->roleId,
        ]);
    }

    /**
     * Get admin session data for authenticated requests
     */
    protected function getAdminSession(): array
    {
        return [
            'user_id'     => $this->userId,
            'username'    => 'testadmin',
            'school_id'   => $this->schoolId,
            'schoolID'    => $this->schoolId,
            'usertypeID'  => 1, // Admin user type
            'is_admin'    => true,
            'logged_in'   => true,
        ];
    }

    /**
     * Get teacher session data for authenticated requests
     */
    protected function getTeacherSession(): array
    {
        return [
            'user_id'     => $this->userId,
            'username'    => 'testteacher',
            'school_id'   => $this->schoolId,
            'schoolID'    => $this->schoolId,
            'usertypeID'  => 2, // Teacher user type
            'is_admin'    => false,
            'logged_in'   => true,
        ];
    }

    /**
     * Get student session data for authenticated requests
     */
    protected function getStudentSession(): array
    {
        return [
            'user_id'     => $this->userId,
            'username'    => 'teststudent',
            'school_id'   => $this->schoolId,
            'schoolID'    => $this->schoolId,
            'usertypeID'  => 3, // Student user type
            'is_admin'    => false,
            'logged_in'   => true,
        ];
    }
}
