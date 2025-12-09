<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Create Teachers Table.
 *
 * Stores teacher profile information separate from the users table.
 * Links to users table via teacher_id (which is a user_id).
 */
class CreateTeachersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'school_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'teacher_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Reference to users.id',
            ],
            'first_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'last_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'employee_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'department' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'subjects' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Comma-separated list of subjects',
            ],
            'qualification' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            'date_of_joining' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive', 'on_leave', 'terminated'],
                'default'    => 'active',
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
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

        $this->forge->addKey('id', true);
        $this->forge->addKey('school_id');
        $this->forge->addKey('teacher_id');
        $this->forge->addKey(['school_id', 'employee_id'], false, true); // Unique employee ID per school
        $this->forge->addKey(['school_id', 'status']);

        // Foreign keys only for MySQL
        if ($this->db->DBDriver === 'MySQLi') {
            $this->forge->addForeignKey('school_id', 'schools', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('teacher_id', 'users', 'id', 'CASCADE', 'CASCADE');
        }

        $attributes = [];
        if ($this->db->DBDriver === 'MySQLi') {
            $attributes = ['ENGINE' => 'InnoDB'];
        }

        $this->forge->createTable('teachers', false, $attributes);
    }

    public function down()
    {
        $this->forge->dropTable('teachers', true);
    }
}
