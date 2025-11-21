<?php

namespace Modules\Foundation\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'            => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'event_key'     => ['type' => 'VARCHAR', 'constraint' => 191],
            'event_type'    => ['type' => 'VARCHAR', 'constraint' => 100],
            'tenant_id'     => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'actor_id'      => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'ip_address'    => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent'    => ['type' => 'TEXT', 'null' => true],
            'request_uri'   => ['type' => 'TEXT', 'null' => true],
            'before_state'  => ['type' => 'LONGTEXT', 'null' => true],
            'after_state'   => ['type' => 'LONGTEXT', 'null' => true],
            'metadata_json' => ['type' => 'LONGTEXT', 'null' => true],
            'previous_hash' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'hash_value'    => ['type' => 'VARCHAR', 'constraint' => 255],
            'created_at'    => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('event_key');
        $this->forge->addKey('created_at');
        $this->forge->createTable('ci4_audit_events', true);

        $this->forge->addField([
            'id'         => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'seal_date'  => ['type' => 'DATE'],
            'hash_value' => ['type' => 'VARCHAR', 'constraint' => 255],
            'sealed_at'  => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('seal_date', false, true);
        $this->forge->createTable('ci4_audit_seals', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ci4_audit_events', true);
        $this->forge->dropTable('ci4_audit_seals', true);
    }
}
