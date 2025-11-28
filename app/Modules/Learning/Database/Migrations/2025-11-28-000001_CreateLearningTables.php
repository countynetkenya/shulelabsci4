<?php

namespace App\Modules\Learning\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates enhanced Learning module tables.
 */
class CreateLearningTables extends Migration
{
    public function up(): void
    {
        // subjects - Subject definitions
        if (!$this->db->tableExists('subjects')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'name' => ['type' => 'VARCHAR', 'constraint' => 100],
                'code' => ['type' => 'VARCHAR', 'constraint' => 20],
                'description' => ['type' => 'TEXT', 'null' => true],
                'department_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'is_core' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['school_id', 'code'], 'uk_school_code');
            $this->forge->createTable('subjects', true);
        }

        // class_subjects - Subject allocation to classes
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'class_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'subject_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'teacher_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'periods_per_week' => ['type' => 'INT', 'constraint' => 11, 'default' => 5],
            'academic_year' => ['type' => 'VARCHAR', 'constraint' => 20],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['class_id', 'subject_id', 'academic_year'], 'uk_class_subject_year');
        $this->forge->createTable('class_subjects', true);

        // timetable_slots - Timetable configuration
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'class_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'subject_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'teacher_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'day_of_week' => ['type' => 'TINYINT', 'constraint' => 1],
            'period_number' => ['type' => 'TINYINT', 'constraint' => 2],
            'start_time' => ['type' => 'TIME'],
            'end_time' => ['type' => 'TIME'],
            'room' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'academic_year' => ['type' => 'VARCHAR', 'constraint' => 20],
            'term' => ['type' => 'VARCHAR', 'constraint' => 20],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['class_id', 'day_of_week', 'period_number'], false, false, 'idx_class_day_period');
        $this->forge->addKey(['teacher_id', 'day_of_week', 'period_number'], false, false, 'idx_teacher_day_period');
        $this->forge->createTable('timetable_slots', true);

        // attendance - Student attendance records
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'student_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'class_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'attendance_date' => ['type' => 'DATE'],
            'status' => ['type' => 'ENUM', 'constraint' => ['present', 'absent', 'late', 'excused', 'half_day']],
            'check_in_time' => ['type' => 'TIME', 'null' => true],
            'check_out_time' => ['type' => 'TIME', 'null' => true],
            'remarks' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'marked_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['student_id', 'attendance_date'], 'uk_student_date');
        $this->forge->addKey(['class_id', 'attendance_date'], false, false, 'idx_class_date');
        $this->forge->addKey(['school_id', 'attendance_date'], false, false, 'idx_school_date');
        $this->forge->createTable('attendance', true);

        // grading_scales - Grading configuration
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'min_score' => ['type' => 'DECIMAL', 'constraint' => '5,2'],
            'max_score' => ['type' => 'DECIMAL', 'constraint' => '5,2'],
            'grade' => ['type' => 'VARCHAR', 'constraint' => 5],
            'points' => ['type' => 'DECIMAL', 'constraint' => '4,2'],
            'remarks' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['school_id', 'grade'], false, false, 'idx_school_grade');
        $this->forge->createTable('grading_scales', true);

        // exams - Examination definitions
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'exam_type' => ['type' => 'ENUM', 'constraint' => ['midterm', 'endterm', 'mock', 'final', 'cat', 'assignment']],
            'academic_year' => ['type' => 'VARCHAR', 'constraint' => 20],
            'term' => ['type' => 'VARCHAR', 'constraint' => 20],
            'start_date' => ['type' => 'DATE'],
            'end_date' => ['type' => 'DATE'],
            'max_score' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 100],
            'weight_percentage' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 100],
            'status' => ['type' => 'ENUM', 'constraint' => ['scheduled', 'ongoing', 'completed', 'cancelled'], 'default' => 'scheduled'],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['school_id', 'academic_year', 'term'], false, false, 'idx_school_year_term');
        $this->forge->createTable('exams', true);

        // exam_results - Student exam scores
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'exam_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'student_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'subject_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'class_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'score' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'grade' => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => true],
            'points' => ['type' => 'DECIMAL', 'constraint' => '4,2', 'null' => true],
            'remarks' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'entered_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['exam_id', 'student_id', 'subject_id'], 'uk_exam_student_subject');
        $this->forge->addKey(['student_id', 'exam_id'], false, false, 'idx_student_exam');
        $this->forge->createTable('exam_results', true);

        // report_cards - Generated report cards
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'student_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'class_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'academic_year' => ['type' => 'VARCHAR', 'constraint' => 20],
            'term' => ['type' => 'VARCHAR', 'constraint' => 20],
            'total_score' => ['type' => 'DECIMAL', 'constraint' => '6,2', 'null' => true],
            'average_score' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'total_points' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'mean_grade' => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => true],
            'class_position' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'stream_position' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'overall_position' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'class_teacher_remarks' => ['type' => 'TEXT', 'null' => true],
            'principal_remarks' => ['type' => 'TEXT', 'null' => true],
            'generated_at' => ['type' => 'DATETIME', 'null' => true],
            'published_at' => ['type' => 'DATETIME', 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['draft', 'generated', 'published'], 'default' => 'draft'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['student_id', 'academic_year', 'term'], 'uk_student_year_term');
        $this->forge->addKey(['class_id', 'academic_year', 'term'], false, false, 'idx_class_year_term');
        $this->forge->createTable('report_cards', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('report_cards', true);
        $this->forge->dropTable('exam_results', true);
        $this->forge->dropTable('exams', true);
        $this->forge->dropTable('grading_scales', true);
        $this->forge->dropTable('attendance', true);
        $this->forge->dropTable('timetable_slots', true);
        $this->forge->dropTable('class_subjects', true);
        $this->forge->dropTable('subjects', true);
    }
}
