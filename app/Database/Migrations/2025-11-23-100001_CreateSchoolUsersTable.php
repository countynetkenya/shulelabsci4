<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSchoolUsersTable extends Migration
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
            'school_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'role_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'is_primary_school' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'joined_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['school_id', 'user_id'], false, true); // Unique composite key
        $this->forge->addKey('school_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey(['school_id', 'user_id', 'role_id']); // Composite index for lookups
        
        // Foreign keys only for MySQL
        if ($this->db->DBDriver === 'MySQLi') {
            $this->forge->addForeignKey('school_id', 'schools', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'CASCADE');
        }
        
        $attributes = [];
        if ($this->db->DBDriver === 'MySQLi') {
            $attributes = ['ENGINE' => 'InnoDB'];
        }
        
        $this->forge->createTable('school_users', false, $attributes);
    }

    public function down()
    {
        $this->forge->dropTable('school_users', true);
    }
}
