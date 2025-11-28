<?php

namespace App\Modules\Audit\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates the audit module tables for comprehensive audit logging.
 */
class CreateAuditTables extends Migration
{
    public function up(): void
    {
        // audit_events - Comprehensive audit trail (enhanced)
        if (!$this->db->tableExists('audit_events')) {
            $this->forge->addField([
                'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
                'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'event_key' => ['type' => 'VARCHAR', 'constraint' => 100],
                'event_type' => ['type' => 'VARCHAR', 'constraint' => 100],
                'entity_type' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'entity_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'before_state' => ['type' => 'JSON', 'null' => true],
                'after_state' => ['type' => 'JSON', 'null' => true],
                'changed_fields' => ['type' => 'JSON', 'null' => true],
                'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
                'user_agent' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
                'request_uri' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
                'trace_id' => ['type' => 'VARCHAR', 'constraint' => 36, 'null' => true],
                'previous_hash' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
                'hash_value' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
                'metadata_json' => ['type' => 'JSON', 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['entity_type', 'entity_id'], false, false, 'idx_entity');
            $this->forge->addKey(['school_id', 'created_at'], false, false, 'idx_school_date');
            $this->forge->addKey('trace_id', false, false, 'idx_trace');
            $this->forge->addKey(['event_type', 'created_at'], false, false, 'idx_type_date');
            $this->forge->createTable('audit_events', true);
        }

        // audit_retention_policies - Configurable retention rules
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'event_type' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'retention_days' => ['type' => 'INT', 'constraint' => 11, 'default' => 365],
            'archive_before_delete' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['school_id', 'event_type'], false, false, 'idx_school_type');
        $this->forge->createTable('audit_retention_policies', true);

        // audit_archives - Archived audit logs
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'archive_date' => ['type' => 'DATE'],
            'event_count' => ['type' => 'INT', 'constraint' => 11],
            'file_path' => ['type' => 'VARCHAR', 'constraint' => 500],
            'file_size_bytes' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'checksum' => ['type' => 'VARCHAR', 'constraint' => 64],
            'archived_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['school_id', 'archive_date'], false, false, 'idx_school_date');
        $this->forge->createTable('audit_archives', true);

        // audit_alerts - Real-time audit alerts configuration
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'event_types' => ['type' => 'JSON'],
            'conditions' => ['type' => 'JSON', 'null' => true],
            'notify_users' => ['type' => 'JSON'],
            'notify_channels' => ['type' => 'JSON'],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('school_id', false, false, 'idx_school');
        $this->forge->createTable('audit_alerts', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('audit_alerts', true);
        $this->forge->dropTable('audit_archives', true);
        $this->forge->dropTable('audit_retention_policies', true);
        $this->forge->dropTable('audit_events', true);
    }
}
