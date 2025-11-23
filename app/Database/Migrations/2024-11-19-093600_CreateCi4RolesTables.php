<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Create CI4 Roles and User Roles Tables.
 *
 * This migration creates the roles and user_roles tables
 * for managing user roles in CI4, and seeds the default roles
 * with their CI3 usertypeID mappings.
 */
class CreateCi4RolesTables extends Migration
{
    public function up()
    {
        // Create roles table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'role_name' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'null' => false,
            ],
            'role_slug' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'null' => false,
                'comment' => 'URL-friendly role identifier',
            ],
            'ci3_usertype_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'comment' => 'CI3 usertypeID for backward compatibility',
            ],
            'description' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('role_slug');
        $this->forge->addKey('ci3_usertype_id');

        // Create table with DB-specific attributes
        $attributes = [];
        if ($this->db->DBDriver === 'MySQLi') {
            $attributes = [
                'ENGINE' => 'InnoDB',
                'CHARSET' => 'utf8mb4',
                'COLLATE' => 'utf8mb4_general_ci',
            ];
        }
        $this->forge->createTable('roles', true, $attributes);

        // Create user_roles pivot table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'role_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['user_id', 'role_id']);
        $this->forge->addKey('user_id');
        $this->forge->addKey('role_id');

        // Create table with DB-specific attributes
        $attributes = [];
        if ($this->db->DBDriver === 'MySQLi') {
            $attributes = [
                'ENGINE' => 'InnoDB',
                'CHARSET' => 'utf8mb4',
                'COLLATE' => 'utf8mb4_general_ci',
            ];
        }
        $this->forge->createTable('user_roles', true, $attributes);

        // Seed default roles
        $this->seedDefaultRoles();
    }

    public function down()
    {
        $this->forge->dropTable('user_roles', true);
        $this->forge->dropTable('roles', true);
    }

    /**
     * Seed default roles with CI3 usertypeID mappings.
     */
    private function seedDefaultRoles(): void
    {
        // Check if roles already exist to prevent duplicates
        $existingCount = $this->db->table('roles')->countAllResults();
        if ($existingCount > 0) {
            return; // Roles already seeded
        }

        $roles = [
            [
                'role_name' => 'Super Admin',
                'role_slug' => 'super_admin',
                'ci3_usertype_id' => 0,
                'description' => 'Full system access across all schools',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'role_name' => 'Admin',
                'role_slug' => 'admin',
                'ci3_usertype_id' => 1,
                'description' => 'School administration',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'role_name' => 'Teacher',
                'role_slug' => 'teacher',
                'ci3_usertype_id' => 2,
                'description' => 'Teaching staff',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'role_name' => 'Student',
                'role_slug' => 'student',
                'ci3_usertype_id' => 3,
                'description' => 'Student account',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'role_name' => 'Parent',
                'role_slug' => 'parent',
                'ci3_usertype_id' => 4,
                'description' => 'Parent/Guardian account',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'role_name' => 'Accountant',
                'role_slug' => 'accountant',
                'ci3_usertype_id' => 5,
                'description' => 'Accounting staff',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'role_name' => 'Librarian',
                'role_slug' => 'librarian',
                'ci3_usertype_id' => 6,
                'description' => 'Library staff',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'role_name' => 'Receptionist',
                'role_slug' => 'receptionist',
                'ci3_usertype_id' => 7,
                'description' => 'Reception staff',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('roles')->insertBatch($roles);
    }
}
