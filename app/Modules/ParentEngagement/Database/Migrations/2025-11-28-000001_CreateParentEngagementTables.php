<?php

namespace App\Modules\ParentEngagement\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates Parent Engagement module tables.
 */
class CreateParentEngagementTables extends Migration
{
    public function up(): void
    {
        // surveys - Survey definitions
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255],
            'description' => ['type' => 'TEXT', 'null' => true],
            'survey_type' => ['type' => 'ENUM', 'constraint' => ['feedback', 'poll', 'evaluation', 'custom']],
            'target_audience' => ['type' => 'ENUM', 'constraint' => ['all_parents', 'class_parents', 'specific']],
            'target_ids' => ['type' => 'JSON', 'null' => true],
            'questions' => ['type' => 'JSON'],
            'is_anonymous' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'start_date' => ['type' => 'DATE', 'null' => true],
            'end_date' => ['type' => 'DATE', 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['draft', 'active', 'closed', 'archived'], 'default' => 'draft'],
            'response_count' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['school_id', 'status'], false, false, 'idx_school_status');
        $this->forge->createTable('surveys', true);

        // survey_responses - Parent survey responses
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'survey_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'responses' => ['type' => 'JSON'],
            'submitted_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('survey_id', false, false, 'idx_survey');
        $this->forge->createTable('survey_responses', true);

        // events - School events
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255],
            'description' => ['type' => 'TEXT', 'null' => true],
            'event_type' => ['type' => 'ENUM', 'constraint' => ['academic', 'sports', 'cultural', 'meeting', 'fundraising', 'other']],
            'venue' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'start_datetime' => ['type' => 'DATETIME'],
            'end_datetime' => ['type' => 'DATETIME', 'null' => true],
            'max_attendees' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'registration_required' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'registration_deadline' => ['type' => 'DATETIME', 'null' => true],
            'fee' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'status' => ['type' => 'ENUM', 'constraint' => ['draft', 'published', 'cancelled', 'completed'], 'default' => 'draft'],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['school_id', 'start_datetime'], false, false, 'idx_school_date');
        $this->forge->createTable('events', true);

        // event_registrations - Event RSVPs
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'event_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'attendees' => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
            'status' => ['type' => 'ENUM', 'constraint' => ['registered', 'attended', 'cancelled', 'no_show'], 'default' => 'registered'],
            'payment_status' => ['type' => 'ENUM', 'constraint' => ['pending', 'paid', 'refunded'], 'default' => 'pending'],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'registered_at' => ['type' => 'DATETIME'],
            'checked_in_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['event_id', 'user_id'], 'uk_event_user');
        $this->forge->createTable('event_registrations', true);

        // conferences - Parent-teacher conference scheduling
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 200],
            'description' => ['type' => 'TEXT', 'null' => true],
            'conference_date' => ['type' => 'DATE'],
            'start_time' => ['type' => 'TIME'],
            'end_time' => ['type' => 'TIME'],
            'slot_duration_minutes' => ['type' => 'INT', 'constraint' => 11, 'default' => 15],
            'venue' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_virtual' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'meeting_link' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['draft', 'open', 'closed', 'completed'], 'default' => 'draft'],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['school_id', 'conference_date'], false, false, 'idx_school_date');
        $this->forge->createTable('conferences', true);

        // conference_slots - Individual time slots
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'conference_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'teacher_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'start_time' => ['type' => 'TIME'],
            'end_time' => ['type' => 'TIME'],
            'parent_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'student_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['available', 'booked', 'completed', 'cancelled'], 'default' => 'available'],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'booked_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['conference_id', 'teacher_id'], false, false, 'idx_conference_teacher');
        $this->forge->createTable('conference_slots', true);

        // volunteers - Parent volunteer management
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'skills' => ['type' => 'JSON', 'null' => true],
            'availability' => ['type' => 'JSON', 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'total_hours' => ['type' => 'DECIMAL', 'constraint' => '6,1', 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['school_id', 'user_id'], 'uk_school_user');
        $this->forge->createTable('volunteers', true);

        // fundraising_campaigns - Fundraising management
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 200],
            'description' => ['type' => 'TEXT', 'null' => true],
            'target_amount' => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'raised_amount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'start_date' => ['type' => 'DATE'],
            'end_date' => ['type' => 'DATE'],
            'status' => ['type' => 'ENUM', 'constraint' => ['draft', 'active', 'completed', 'cancelled'], 'default' => 'draft'],
            'donor_count' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['school_id', 'status'], false, false, 'idx_school_status');
        $this->forge->createTable('fundraising_campaigns', true);

        // donations - Campaign donations
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'campaign_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'donor_name' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'amount' => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'payment_reference' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'is_anonymous' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'message' => ['type' => 'TEXT', 'null' => true],
            'donated_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('campaign_id', false, false, 'idx_campaign');
        $this->forge->createTable('donations', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('donations', true);
        $this->forge->dropTable('fundraising_campaigns', true);
        $this->forge->dropTable('volunteers', true);
        $this->forge->dropTable('conference_slots', true);
        $this->forge->dropTable('conferences', true);
        $this->forge->dropTable('event_registrations', true);
        $this->forge->dropTable('events', true);
        $this->forge->dropTable('survey_responses', true);
        $this->forge->dropTable('surveys', true);
    }
}
