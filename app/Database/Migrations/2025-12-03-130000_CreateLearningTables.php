<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLearningTables extends Migration
{
    public function up()
    {
        // 1. Learning Courses
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'school_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'teacher_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'published', 'archived'],
                'default'    => 'draft',
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('school_id', 'schools', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('teacher_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('learning_courses');

        // 2. Learning Lessons
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'course_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'content' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'video_url' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'sequence_order' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('course_id', 'learning_courses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('learning_lessons');

        // 3. Learning Enrollments
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'school_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'student_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'course_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'completed', 'dropped'],
                'default'    => 'active',
            ],
            'enrolled_at' => [
                'type'    => 'DATETIME',
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('school_id', 'schools', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('student_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('course_id', 'learning_courses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('learning_enrollments');

        // 4. Learning Progress
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'enrollment_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'lesson_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'completed_at' => [
                'type'    => 'DATETIME',
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('enrollment_id', 'learning_enrollments', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('lesson_id', 'learning_lessons', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addUniqueKey(['enrollment_id', 'lesson_id']);
        $this->forge->createTable('learning_progress');
    }

    public function down()
    {
        $this->forge->dropTable('learning_progress', true);
        $this->forge->dropTable('learning_enrollments', true);
        $this->forge->dropTable('learning_lessons', true);
        $this->forge->dropTable('learning_courses', true);
    }
}
