<?php

namespace App\Modules\Admissions\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates Admissions module tables.
 */
class CreateAdmissionsTables extends Migration
{
    public function up(): void
    {
        // applications - Student applications
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'application_number' => ['type' => 'VARCHAR', 'constraint' => 50],
            'academic_year' => ['type' => 'VARCHAR', 'constraint' => 20],
            'term' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'class_applied' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'student_first_name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'student_last_name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'student_dob' => ['type' => 'DATE'],
            'student_gender' => ['type' => 'ENUM', 'constraint' => ['male', 'female', 'other']],
            'previous_school' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'parent_first_name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'parent_last_name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'parent_email' => ['type' => 'VARCHAR', 'constraint' => 255],
            'parent_phone' => ['type' => 'VARCHAR', 'constraint' => 20],
            'parent_relationship' => ['type' => 'ENUM', 'constraint' => ['father', 'mother', 'guardian']],
            'address' => ['type' => 'TEXT', 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['submitted', 'under_review', 'interview_scheduled', 'test_scheduled', 'accepted', 'rejected', 'waitlisted', 'enrolled'], 'default' => 'submitted'],
            'stage_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'reviewed_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'reviewed_at' => ['type' => 'DATETIME', 'null' => true],
            'decision_notes' => ['type' => 'TEXT', 'null' => true],
            'application_fee_paid' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'fee_payment_ref' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['school_id', 'application_number'], 'uk_school_app_number');
        $this->forge->addKey(['school_id', 'status'], false, false, 'idx_school_status');
        $this->forge->addKey(['school_id', 'academic_year'], false, false, 'idx_school_year');
        $this->forge->createTable('applications', true);

        // application_documents - Uploaded documents
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'application_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'document_type' => ['type' => 'VARCHAR', 'constraint' => 50],
            'file_name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'file_path' => ['type' => 'VARCHAR', 'constraint' => 500],
            'file_size' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'mime_type' => ['type' => 'VARCHAR', 'constraint' => 100],
            'verified' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'verified_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'verified_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('application_id', false, false, 'idx_application');
        $this->forge->createTable('application_documents', true);

        // application_stages - Workflow stages configuration
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'sequence' => ['type' => 'INT', 'constraint' => 11],
            'description' => ['type' => 'TEXT', 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['school_id', 'sequence'], false, false, 'idx_school_sequence');
        $this->forge->createTable('application_stages', true);

        // entrance_tests - Test scheduling
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'test_date' => ['type' => 'DATE'],
            'test_time' => ['type' => 'TIME'],
            'venue' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'max_candidates' => ['type' => 'INT', 'constraint' => 11, 'default' => 50],
            'registered_count' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'status' => ['type' => 'ENUM', 'constraint' => ['scheduled', 'ongoing', 'completed', 'cancelled'], 'default' => 'scheduled'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['school_id', 'test_date'], false, false, 'idx_school_date');
        $this->forge->createTable('entrance_tests', true);

        // test_registrations - Test participants
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'test_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'application_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'attendance_status' => ['type' => 'ENUM', 'constraint' => ['registered', 'present', 'absent'], 'default' => 'registered'],
            'score' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'grade' => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => true],
            'remarks' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['test_id', 'application_id'], 'uk_test_application');
        $this->forge->createTable('test_registrations', true);

        // interviews - Interview scheduling
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'application_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'interview_date' => ['type' => 'DATE'],
            'interview_time' => ['type' => 'TIME'],
            'interviewer_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'venue' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['scheduled', 'completed', 'cancelled', 'no_show'], 'default' => 'scheduled'],
            'rating' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'recommendation' => ['type' => 'ENUM', 'constraint' => ['strongly_recommend', 'recommend', 'neutral', 'not_recommend'], 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('application_id', false, false, 'idx_application');
        $this->forge->createTable('interviews', true);

        // waitlists - Waitlist management
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'application_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'class_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'position' => ['type' => 'INT', 'constraint' => 11],
            'priority_score' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'offer_sent' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'offer_sent_at' => ['type' => 'DATETIME', 'null' => true],
            'offer_expires_at' => ['type' => 'DATETIME', 'null' => true],
            'offer_response' => ['type' => 'ENUM', 'constraint' => ['pending', 'accepted', 'declined', 'expired'], 'default' => 'pending'],
            'response_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['school_id', 'class_id', 'position'], false, false, 'idx_school_class_position');
        $this->forge->createTable('waitlists', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('waitlists', true);
        $this->forge->dropTable('interviews', true);
        $this->forge->dropTable('test_registrations', true);
        $this->forge->dropTable('entrance_tests', true);
        $this->forge->dropTable('application_stages', true);
        $this->forge->dropTable('application_documents', true);
        $this->forge->dropTable('applications', true);
    }
}
