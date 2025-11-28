<?php

namespace App\Modules\Governance\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates Governance module tables for board management.
 */
class CreateGovernanceTables extends Migration
{
    public function up(): void
    {
        // board_members - Board of directors/governors
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'email' => ['type' => 'VARCHAR', 'constraint' => 255],
            'phone' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'position' => ['type' => 'VARCHAR', 'constraint' => 100],
            'bio' => ['type' => 'TEXT', 'null' => true],
            'photo_url' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'term_start' => ['type' => 'DATE'],
            'term_end' => ['type' => 'DATE', 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'committees' => ['type' => 'JSON', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['school_id', 'is_active'], false, false, 'idx_school_active');
        $this->forge->createTable('board_members', true);

        // board_meetings - Meeting records
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 200],
            'meeting_type' => ['type' => 'ENUM', 'constraint' => ['regular', 'special', 'emergency', 'agm', 'committee']],
            'committee_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'meeting_date' => ['type' => 'DATE'],
            'start_time' => ['type' => 'TIME'],
            'end_time' => ['type' => 'TIME', 'null' => true],
            'venue' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_virtual' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'meeting_link' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'agenda' => ['type' => 'JSON', 'null' => true],
            'minutes' => ['type' => 'LONGTEXT', 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['scheduled', 'in_progress', 'completed', 'cancelled'], 'default' => 'scheduled'],
            'quorum_required' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'attendee_count' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['school_id', 'meeting_date'], false, false, 'idx_school_date');
        $this->forge->createTable('board_meetings', true);

        // meeting_attendance - Meeting attendance tracking
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'meeting_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'member_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['present', 'absent', 'excused', 'proxy'], 'default' => 'present'],
            'proxy_name' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'arrival_time' => ['type' => 'TIME', 'null' => true],
            'departure_time' => ['type' => 'TIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['meeting_id', 'member_id'], 'uk_meeting_member');
        $this->forge->createTable('meeting_attendance', true);

        // board_resolutions - Formal resolutions
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'meeting_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'resolution_number' => ['type' => 'VARCHAR', 'constraint' => 50],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255],
            'content' => ['type' => 'LONGTEXT'],
            'category' => ['type' => 'VARCHAR', 'constraint' => 50],
            'proposed_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'seconded_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'votes_for' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'votes_against' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'votes_abstained' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'status' => ['type' => 'ENUM', 'constraint' => ['draft', 'proposed', 'passed', 'rejected', 'tabled', 'implemented'], 'default' => 'draft'],
            'effective_date' => ['type' => 'DATE', 'null' => true],
            'implementation_notes' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['school_id', 'resolution_number'], 'uk_school_resolution');
        $this->forge->createTable('board_resolutions', true);

        // committees - Board committees
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'description' => ['type' => 'TEXT', 'null' => true],
            'chair_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'member_ids' => ['type' => 'JSON'],
            'responsibilities' => ['type' => 'JSON', 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('school_id', false, false, 'idx_school');
        $this->forge->createTable('committees', true);

        // policies - School policies
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'policy_number' => ['type' => 'VARCHAR', 'constraint' => 50],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255],
            'category' => ['type' => 'VARCHAR', 'constraint' => 50],
            'content' => ['type' => 'LONGTEXT'],
            'summary' => ['type' => 'TEXT', 'null' => true],
            'version' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => '1.0'],
            'status' => ['type' => 'ENUM', 'constraint' => ['draft', 'under_review', 'approved', 'archived'], 'default' => 'draft'],
            'approved_by_resolution_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'effective_date' => ['type' => 'DATE', 'null' => true],
            'review_date' => ['type' => 'DATE', 'null' => true],
            'document_url' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['school_id', 'policy_number'], 'uk_school_policy');
        $this->forge->createTable('policies', true);

        // board_documents - Document repository
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'meeting_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255],
            'document_type' => ['type' => 'ENUM', 'constraint' => ['agenda', 'minutes', 'report', 'policy', 'financial', 'other']],
            'file_path' => ['type' => 'VARCHAR', 'constraint' => 500],
            'file_size' => ['type' => 'INT', 'constraint' => 11],
            'mime_type' => ['type' => 'VARCHAR', 'constraint' => 100],
            'is_confidential' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'access_roles' => ['type' => 'JSON', 'null' => true],
            'uploaded_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['school_id', 'document_type'], false, false, 'idx_school_type');
        $this->forge->createTable('board_documents', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('board_documents', true);
        $this->forge->dropTable('policies', true);
        $this->forge->dropTable('committees', true);
        $this->forge->dropTable('board_resolutions', true);
        $this->forge->dropTable('meeting_attendance', true);
        $this->forge->dropTable('board_meetings', true);
        $this->forge->dropTable('board_members', true);
    }
}
