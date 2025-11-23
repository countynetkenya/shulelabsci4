<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSchoolClassesTable extends Migration
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
            'class_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'grade_level' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'section' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
            ],
            'class_teacher_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'academic_year' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'max_capacity' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 40,
            ],
            'room_number' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
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
        $this->forge->addKey('school_id');
        $this->forge->addKey('class_teacher_id');

        // Foreign keys only for MySQL
        if ($this->db->DBDriver === 'MySQLi') {
            $this->forge->addForeignKey('school_id', 'schools', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('class_teacher_id', 'users', 'id', 'SET NULL', 'CASCADE');
        }

        $attributes = [];
        if ($this->db->DBDriver === 'MySQLi') {
            $attributes = ['ENGINE' => 'InnoDB'];
        }

        $this->forge->createTable('school_classes', false, $attributes);
    }

    public function down()
    {
        $this->forge->dropTable('school_classes', true);
    }
}
