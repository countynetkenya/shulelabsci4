<?php

declare(strict_types=1);

namespace Modules\Foundation\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\ConnectionInterface;
use Config\Database;
use RuntimeException;

/**
 * InstallService handles the initial bootstrap of the ShuleLabs application.
 * Creates the first organisation, school, and admin user.
 */
class InstallService
{
    /**
     * @phpstan-var BaseConnection<object, object>
     */
    private BaseConnection $db;

    /**
     * @phpstan-param ConnectionInterface<object, object>|null $connection
     */
    public function __construct(?ConnectionInterface $connection = null)
    {
        $this->db = $connection instanceof BaseConnection ? $connection : Database::connect();
    }

    /**
     * Check if the application is already installed by checking for existing tenants.
     */
    public function isInstalled(): bool
    {
        // Check .env flag first
        $envInstalled = env('app.installed', false);
        if (filter_var($envInstalled, FILTER_VALIDATE_BOOLEAN)) {
            return true;
        }

        // Fallback: check if there are any organisation or school tenants
        try {
            $count = $this->db->table('tenant_catalog')
                ->whereIn('tenant_type', ['organisation', 'school'])
                ->countAllResults();
            
            return $count > 0;
        } catch (\Throwable $e) {
            // If table doesn't exist, migrations haven't been run
            return false;
        }
    }

    /**
     * Verify database connection is working.
     */
    public function checkDatabaseConnection(): bool
    {
        try {
            $this->db->query('SELECT 1');
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Check if required migrations have been run.
     *
     * @return array{success: bool, missing: list<string>}
     */
    public function checkMigrations(): array
    {
        $requiredTables = [
            'tenant_catalog',
            'users',
            'roles',
            'user_roles',
        ];

        $missing = [];
        
        foreach ($requiredTables as $table) {
            if (!$this->db->tableExists($table)) {
                $missing[] = $table;
            }
        }

        return [
            'success' => count($missing) === 0,
            'missing' => $missing,
        ];
    }

    /**
     * Create organisation and school tenants.
     *
     * @param array{organisation_name: string, organisation_code?: string, school_name: string, school_code?: string, country?: string, curriculum?: string} $data
     * @return array{organisation_id: string, school_id: string}
     * @throws RuntimeException
     */
    public function createTenants(array $data): array
    {
        $this->db->transStart();

        try {
            // Generate IDs
            $orgId = $data['organisation_code'] ?? 'org-' . uniqid();
            $schoolId = $data['school_code'] ?? 'school-' . uniqid();

            // Create organisation tenant
            $orgMetadata = [];
            if (!empty($data['country'])) {
                $orgMetadata['country'] = $data['country'];
            }

            $this->db->table('tenant_catalog')->insert([
                'id' => $orgId,
                'tenant_type' => 'organisation',
                'name' => $data['organisation_name'],
                'metadata' => !empty($orgMetadata) ? json_encode($orgMetadata) : null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            // Create school tenant
            $schoolMetadata = ['organisation_id' => $orgId];
            if (!empty($data['curriculum'])) {
                $schoolMetadata['curriculum'] = $data['curriculum'];
            }

            $this->db->table('tenant_catalog')->insert([
                'id' => $schoolId,
                'tenant_type' => 'school',
                'name' => $data['school_name'],
                'metadata' => json_encode($schoolMetadata),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('Failed to create tenants');
            }

            return [
                'organisation_id' => $orgId,
                'school_id' => $schoolId,
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw new RuntimeException('Failed to create tenants: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Create the first admin user.
     *
     * @param array{full_name: string, email: string, username: string, password: string, school_id: string} $data
     * @return int User ID
     * @throws RuntimeException
     */
    public function createAdminUser(array $data): int
    {
        $this->db->transStart();

        try {
            // Check if username already exists
            $existing = $this->db->table('users')
                ->where('username', $data['username'])
                ->get()
                ->getRow();

            if ($existing) {
                throw new RuntimeException('Username already exists');
            }

            // Check if email already exists
            if (!empty($data['email'])) {
                $existingEmail = $this->db->table('users')
                    ->where('email', $data['email'])
                    ->get()
                    ->getRow();

                if ($existingEmail) {
                    throw new RuntimeException('Email already exists');
                }
            }

            // Hash password using SHA-512 (CI3 compatible initially, can be upgraded later)
            // Note: SHA-512 without salt is used for CI3 compatibility during migration.
            // For new installations, consider using password_hash() with PASSWORD_DEFAULT.
            // However, this maintains consistency with the existing CI3 schema.
            $passwordHash = hash('sha512', $data['password']);

            // Create user
            $this->db->table('users')->insert([
                'username' => $data['username'],
                'email' => $data['email'],
                'password_hash' => $passwordHash,
                'full_name' => $data['full_name'],
                'schoolID' => $data['school_id'],
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $userId = (int) $this->db->insertID();

            // Get Super Admin role
            $superAdminRole = $this->db->table('roles')
                ->where('role_slug', 'super_admin')
                ->get()
                ->getRow();

            if (!$superAdminRole) {
                throw new RuntimeException('Super Admin role not found. Please run migrations.');
            }

            // Assign Super Admin role to user
            $this->db->table('user_roles')->insert([
                'user_id' => $userId,
                'role_id' => $superAdminRole->id,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('Failed to create admin user');
            }

            return $userId;
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw new RuntimeException('Failed to create admin user: ' . $e->getMessage(), 0, $e);
        }
    }
}
