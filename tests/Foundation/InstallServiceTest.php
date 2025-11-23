<?php

declare(strict_types=1);

namespace Tests\Ci4\Foundation;

use Modules\Foundation\Services\InstallService;
use RuntimeException;

class InstallServiceTest extends FoundationDatabaseTestCase
{
    private InstallService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InstallService($this->db);
    }

    public function testIsInstalledReturnsFalseWhenNoTenants(): void
    {
        $this->assertFalse($this->service->isInstalled());
    }

    public function testIsInstalledReturnsTrueWhenTenantsExist(): void
    {
        $this->db->table('tenant_catalog')->insert([
            'id' => 'org-test',
            'tenant_type' => 'organisation',
            'name' => 'Test Org',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->assertTrue($this->service->isInstalled());
    }

    public function testCheckDatabaseConnectionReturnsTrue(): void
    {
        $this->assertTrue($this->service->checkDatabaseConnection());
    }

    public function testCheckMigrationsReturnsSuccess(): void
    {
        $result = $this->service->checkMigrations();

        $this->assertTrue($result['success']);
        $this->assertEmpty($result['missing']);
    }

    public function testCreateTenantsSucceeds(): void
    {
        $data = [
            'organisation_name' => 'Test Organisation',
            'organisation_code' => 'test-org',
            'school_name' => 'Test School',
            'school_code' => 'test-school',
            'country' => 'Kenya',
            'curriculum' => 'CBC',
        ];

        $result = $this->service->createTenants($data);

        $this->assertSame('test-org', $result['organisation_id']);
        $this->assertSame('test-school', $result['school_id']);

        // Verify organisation was created
        $org = $this->db->table('tenant_catalog')
            ->where('id', 'test-org')
            ->where('tenant_type', 'organisation')
            ->get()
            ->getRow();

        $this->assertNotNull($org);
        $this->assertSame('Test Organisation', $org->name);

        // Verify school was created
        $school = $this->db->table('tenant_catalog')
            ->where('id', 'test-school')
            ->where('tenant_type', 'school')
            ->get()
            ->getRow();

        $this->assertNotNull($school);
        $this->assertSame('Test School', $school->name);

        // Verify metadata
        $schoolMetadata = json_decode($school->metadata, true);
        $this->assertSame('test-org', $schoolMetadata['organisation_id']);
        $this->assertSame('CBC', $schoolMetadata['curriculum']);
    }

    public function testCreateTenantsGeneratesIdsWhenNotProvided(): void
    {
        $data = [
            'organisation_name' => 'Test Organisation',
            'school_name' => 'Test School',
        ];

        $result = $this->service->createTenants($data);

        $this->assertStringStartsWith('org-', $result['organisation_id']);
        $this->assertStringStartsWith('school-', $result['school_id']);
    }

    public function testCreateAdminUserSucceeds(): void
    {
        // First create roles table and seed it
        $this->seedRoles();

        // Create school tenant
        $this->db->table('tenant_catalog')->insert([
            'id' => 'school-1',
            'tenant_type' => 'school',
            'name' => 'Test School',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $data = [
            'full_name' => 'Admin User',
            'email' => 'admin@test.com',
            'username' => 'admin',
            'password' => 'password123',
            'school_id' => 'school-1',
        ];

        $userId = $this->service->createAdminUser($data);

        $this->assertGreaterThan(0, $userId);

        // Verify user was created
        $user = $this->db->table('users')->where('id', $userId)->get()->getRow();

        $this->assertNotNull($user);
        $this->assertSame('admin', $user->username);
        $this->assertSame('admin@test.com', $user->email);
        $this->assertSame('Admin User', $user->full_name);
        $this->assertSame('school-1', $user->schoolID);

        // Verify password hash
        $expectedHash = hash('sha512', 'password123');
        $this->assertSame($expectedHash, $user->password_hash);

        // Verify role assignment
        $roleAssignment = $this->db->table('user_roles')
            ->where('user_id', $userId)
            ->get()
            ->getRow();

        $this->assertNotNull($roleAssignment);

        // Verify it's the super admin role
        $role = $this->db->table('roles')
            ->where('id', $roleAssignment->role_id)
            ->get()
            ->getRow();

        $this->assertSame('super_admin', $role->role_slug);
    }

    public function testCreateAdminUserThrowsWhenUsernameExists(): void
    {
        $this->seedRoles();

        // Create existing user
        $this->db->table('users')->insert([
            'username' => 'admin',
            'email' => 'other@test.com',
            'password_hash' => hash('sha512', 'test'),
            'full_name' => 'Other User',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $data = [
            'full_name' => 'Admin User',
            'email' => 'admin@test.com',
            'username' => 'admin',
            'password' => 'password123',
            'school_id' => 'school-1',
        ];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Username already exists');

        $this->service->createAdminUser($data);
    }

    public function testCreateAdminUserThrowsWhenEmailExists(): void
    {
        $this->seedRoles();

        // Create existing user
        $this->db->table('users')->insert([
            'username' => 'other',
            'email' => 'admin@test.com',
            'password_hash' => hash('sha512', 'test'),
            'full_name' => 'Other User',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $data = [
            'full_name' => 'Admin User',
            'email' => 'admin@test.com',
            'username' => 'admin',
            'password' => 'password123',
            'school_id' => 'school-1',
        ];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Email already exists');

        $this->service->createAdminUser($data);
    }

    private function seedRoles(): void
    {
        // Create roles table
        $prefix = $this->db->getPrefix();
        $this->db->simpleQuery("DROP TABLE IF EXISTS {$prefix}roles");
        $this->db->simpleQuery("DROP TABLE IF EXISTS {$prefix}user_roles");
        $this->db->simpleQuery("DROP TABLE IF EXISTS {$prefix}users");

        $this->db->simpleQuery(<<<SQL
CREATE TABLE {$prefix}roles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    role_name VARCHAR(40) NOT NULL,
    role_slug VARCHAR(40) NOT NULL,
    ci3_usertype_id INTEGER NOT NULL,
    description VARCHAR(255),
    created_at DATETIME,
    updated_at DATETIME
)
SQL);

        $this->db->simpleQuery(<<<SQL
CREATE TABLE {$prefix}user_roles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    role_id INTEGER NOT NULL,
    created_at DATETIME
)
SQL);

        $this->db->simpleQuery(<<<SQL
CREATE TABLE {$prefix}users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(40) NOT NULL,
    email VARCHAR(40),
    password_hash VARCHAR(128) NOT NULL,
    full_name VARCHAR(60) NOT NULL,
    photo VARCHAR(200),
    schoolID VARCHAR(255),
    ci3_user_id INTEGER,
    ci3_user_table VARCHAR(40),
    is_active TINYINT DEFAULT 1,
    created_at DATETIME,
    updated_at DATETIME
)
SQL);

        // Insert super admin role
        $this->db->table('roles')->insert([
            'role_name' => 'Super Admin',
            'role_slug' => 'super_admin',
            'ci3_usertype_id' => 0,
            'description' => 'Full system access',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
