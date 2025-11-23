<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStudentEnrollmentsTable extends Migration
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
            'student_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'school_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'class_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'enrollment_date' => [
                'type' => 'DATE',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'suspended', 'graduated', 'transferred', 'withdrawn'],
                'default' => 'active',
            ],
            'admission_number' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'parent_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
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

        $this->forge->addKey('id', true);
        $this->forge->addKey(['school_id', 'admission_number'], false, true); // Unique admission per school
        $this->forge->addKey('student_id');
        $this->forge->addKey('school_id');
        $this->forge->addKey('class_id');
        $this->forge->addKey(['school_id', 'status', 'student_id']); // Active enrollments lookup

        // Foreign keys only for MySQL
        if ($this->db->DBDriver === 'MySQLi') {
            $this->forge->addForeignKey('student_id', 'users', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('school_id', 'schools', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('class_id', 'school_classes', 'id', 'SET NULL', 'CASCADE');
            $this->forge->addForeignKey('parent_id', 'users', 'id', 'SET NULL', 'CASCADE');
        }

        $attributes = [];
        if ($this->db->DBDriver === 'MySQLi') {
            $attributes = ['ENGINE' => 'InnoDB'];
        }

        $this->forge->createTable('student_enrollments', false, $attributes);
    }

    public function down()
    {
        $this->forge->dropTable('student_enrollments', true);
    }
}
