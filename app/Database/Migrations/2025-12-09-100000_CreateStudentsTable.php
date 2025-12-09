<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Create Students Table.
 *
 * Stores student profile information separate from the users table.
 * Links to users table via student_id (which is a user_id).
 */
class CreateStudentsTable extends Migration
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
            'student_id' => [
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
            'class_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'admission_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'date_of_birth' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'gender' => [
                'type'       => 'ENUM',
                'constraint' => ['male', 'female', 'other'],
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive', 'graduated', 'transferred', 'suspended'],
                'default'    => 'active',
            ],
            'parent_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            'parent_phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'parent_email' => [
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
        $this->forge->addKey('student_id');
        $this->forge->addKey(['school_id', 'admission_number'], false, true); // Unique admission per school
        $this->forge->addKey(['school_id', 'status']);

        // Foreign keys only for MySQL
        if ($this->db->DBDriver === 'MySQLi') {
            $this->forge->addForeignKey('school_id', 'schools', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('student_id', 'users', 'id', 'CASCADE', 'CASCADE');
        }

        $attributes = [];
        if ($this->db->DBDriver === 'MySQLi') {
            $attributes = ['ENGINE' => 'InnoDB'];
        }

        $this->forge->createTable('students', false, $attributes);
    }

    public function down()
    {
        $this->forge->dropTable('students', true);
    }
}
