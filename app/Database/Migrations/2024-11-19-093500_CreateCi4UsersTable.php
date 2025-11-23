<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Create CI4 Users Table
 * 
 * This migration creates the users table that will serve as the
 * normalized identity store for all user types in CI4, replacing the
 * CI3 multi-table approach (student, teacher, parents, user, systemadmin).
 */
class CreateCi4UsersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'username' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'null' => false,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'null' => true,
            ],
            'password_hash' => [
                'type' => 'VARCHAR',
                'constraint' => 128,
                'null' => false,
                'comment' => 'CI3-compatible SHA-512 hash initially, can be upgraded to bcrypt',
            ],
            'full_name' => [
                'type' => 'VARCHAR',
                'constraint' => 60,
                'null' => false,
            ],
            'photo' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => true,
            ],
            'schoolID' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Comma-separated list of school IDs for backward compatibility',
            ],
            'ci3_user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Original CI3 user ID for migration tracking',
            ],
            'ci3_user_table' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'null' => true,
                'comment' => 'Original CI3 table name (student, teacher, parents, user, systemadmin)',
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'null' => false,
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
        $this->forge->addUniqueKey('username');
        $this->forge->addKey('email');
        $this->forge->addKey(['ci3_user_table', 'ci3_user_id']);
        $this->forge->addKey('is_active');

        // Create table with DB-specific attributes
        $attributes = [];
        if ($this->db->DBDriver === 'MySQLi') {
            $attributes = [
                'ENGINE' => 'InnoDB',
                'CHARSET' => 'utf8mb4',
                'COLLATE' => 'utf8mb4_general_ci',
            ];
        }
        $this->forge->createTable('users', true, $attributes);
    }

    public function down()
    {
        $this->forge->dropTable('users', true);
    }
}
