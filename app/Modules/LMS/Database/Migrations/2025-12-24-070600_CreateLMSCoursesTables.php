<?php

namespace Modules\LMS\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates LMS courses and related tables.
 */
class CreateLMSCoursesTables extends Migration
{
    public function up(): void
    {
        // learning_courses - LMS course definitions
        if (!$this->db->tableExists('learning_courses')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'teacher_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'title' => ['type' => 'VARCHAR', 'constraint' => 255],
                'description' => ['type' => 'TEXT', 'null' => true],
                'modules' => ['type' => 'TEXT', 'null' => true],
                'status' => ['type' => 'ENUM', 'constraint' => ['draft', 'published', 'archived'], 'default' => 'draft'],
                'instructor_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
                'deleted_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['school_id', 'status'], false, false, 'idx_school_status');
            $this->forge->createTable('learning_courses', true);
        }

        // learning_lessons - Course lessons
        if (!$this->db->tableExists('learning_lessons')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'course_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'title' => ['type' => 'VARCHAR', 'constraint' => 255],
                'content' => ['type' => 'TEXT', 'null' => true],
                'order_index' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'duration_minutes' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
                'deleted_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('course_id', false, false, 'idx_course');
            $this->forge->createTable('learning_lessons', true);
        }

        // learning_enrollments - Student enrollments
        if (!$this->db->tableExists('learning_enrollments')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'course_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'student_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'enrolled_at' => ['type' => 'DATETIME', 'null' => true],
                'completed_at' => ['type' => 'DATETIME', 'null' => true],
                'status' => ['type' => 'ENUM', 'constraint' => ['active', 'completed', 'dropped'], 'default' => 'active'],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['course_id', 'student_id'], 'uk_course_student');
            $this->forge->createTable('learning_enrollments', true);
        }

        // learning_progress - Student lesson progress
        if (!$this->db->tableExists('learning_progress')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'enrollment_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'lesson_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'is_completed' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'completed_at' => ['type' => 'DATETIME', 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['enrollment_id', 'lesson_id'], 'uk_enrollment_lesson');
            $this->forge->createTable('learning_progress', true);
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('learning_progress', true);
        $this->forge->dropTable('learning_enrollments', true);
        $this->forge->dropTable('learning_lessons', true);
        $this->forge->dropTable('learning_courses', true);
    }
}
