<?php

namespace App\Modules\Security\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates the security module tables: roles, permissions, role_permissions, user_roles,
 * two_factor_auth, login_attempts, password_policies, ip_whitelist.
 */
class CreateSecurityTables extends Migration
{
    public function up(): void
    {
        // roles - User roles
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 100],
            'description' => ['type' => 'TEXT', 'null' => true],
            'is_system' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'parent_role_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'level' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['school_id', 'slug'], 'uk_school_slug');
        $this->forge->addKey('level', false, false, 'idx_level');
        $this->forge->createTable('roles', true);

        // permissions - System permissions
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 100],
            'module' => ['type' => 'VARCHAR', 'constraint' => 50],
            'description' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug', 'uk_slug');
        $this->forge->addKey('module', false, false, 'idx_module');
        $this->forge->createTable('permissions', true);

        // role_permissions - Role-permission mapping
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'role_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'permission_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['role_id', 'permission_id'], 'uk_role_permission');
        $this->forge->createTable('role_permissions', true);

        // user_roles - User-role assignments
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'role_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'assigned_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'assigned_at' => ['type' => 'DATETIME', 'null' => true],
            'expires_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'role_id', 'school_id'], 'uk_user_role_school');
        $this->forge->createTable('user_roles', true);

        // two_factor_auth - 2FA configuration per user
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'method' => ['type' => 'ENUM', 'constraint' => ['totp', 'sms', 'email'], 'default' => 'totp'],
            'secret_encrypted' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'backup_codes' => ['type' => 'JSON', 'null' => true],
            'is_enabled' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'verified_at' => ['type' => 'DATETIME', 'null' => true],
            'last_used_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('user_id', 'uk_user');
        $this->forge->createTable('two_factor_auth', true);

        // login_attempts - Failed login tracking
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'identifier' => ['type' => 'VARCHAR', 'constraint' => 255],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45],
            'user_agent' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'attempt_type' => ['type' => 'ENUM', 'constraint' => ['login', '2fa', 'password_reset'], 'default' => 'login'],
            'was_successful' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'failure_reason' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['identifier', 'created_at'], false, false, 'idx_identifier');
        $this->forge->addKey(['ip_address', 'created_at'], false, false, 'idx_ip');
        $this->forge->createTable('login_attempts', true);

        // password_policies - School-specific password requirements
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'min_length' => ['type' => 'INT', 'constraint' => 11, 'default' => 8],
            'require_uppercase' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'require_lowercase' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'require_number' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'require_special' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'prevent_reuse' => ['type' => 'INT', 'constraint' => 11, 'default' => 5],
            'max_age_days' => ['type' => 'INT', 'constraint' => 11, 'default' => 90],
            'lockout_attempts' => ['type' => 'INT', 'constraint' => 11, 'default' => 5],
            'lockout_duration_minutes' => ['type' => 'INT', 'constraint' => 11, 'default' => 30],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('school_id', 'uk_school');
        $this->forge->createTable('password_policies', true);

        // ip_whitelist - IP-based access control
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45],
            'ip_range' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'description' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'applies_to' => ['type' => 'ENUM', 'constraint' => ['all', 'admin', 'api'], 'default' => 'admin'],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('ip_address', false, false, 'idx_ip');
        $this->forge->createTable('ip_whitelist', true);

        // rate_limits - Rate limiting tracking
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'key' => ['type' => 'VARCHAR', 'constraint' => 255],
            'hits' => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
            'expires_at' => ['type' => 'DATETIME'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('key', 'uk_key');
        $this->forge->addKey('expires_at', false, false, 'idx_expires');
        $this->forge->createTable('rate_limits', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('rate_limits', true);
        $this->forge->dropTable('ip_whitelist', true);
        $this->forge->dropTable('password_policies', true);
        $this->forge->dropTable('login_attempts', true);
        $this->forge->dropTable('two_factor_auth', true);
        $this->forge->dropTable('user_roles', true);
        $this->forge->dropTable('role_permissions', true);
        $this->forge->dropTable('permissions', true);
        $this->forge->dropTable('roles', true);
    }
}
