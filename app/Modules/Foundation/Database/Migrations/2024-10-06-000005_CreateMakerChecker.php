<?php

namespace Modules\Foundation\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMakerChecker extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'               => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'        => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'action_key'       => ['type' => 'VARCHAR', 'constraint' => 150],
            'status'           => ['type' => 'VARCHAR', 'constraint' => 32],
            'payload_json'     => ['type' => 'LONGTEXT'],
            'maker_id'         => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'checker_id'       => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'rejection_reason' => ['type' => 'TEXT', 'null' => true],
            'submitted_at'     => ['type' => 'DATETIME'],
            'processed_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('action_key');
        $this->forge->addKey('status');
        $this->forge->createTable('ci4_maker_checker_requests', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ci4_maker_checker_requests', true);
    }
}
