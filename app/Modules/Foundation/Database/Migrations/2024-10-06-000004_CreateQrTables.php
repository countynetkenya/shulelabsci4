<?php

namespace Modules\Foundation\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQrTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'            => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'resource_type' => ['type' => 'VARCHAR', 'constraint' => 100],
            'resource_id'   => ['type' => 'VARCHAR', 'constraint' => 100],
            'tenant_id'     => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'token'         => ['type' => 'VARCHAR', 'constraint' => 191],
            'issued_at'     => ['type' => 'DATETIME'],
            'expires_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('token', false, true);
        $this->forge->addKey('resource_id');
        $this->forge->createTable('qr_tokens', true);

        $this->forge->addField([
            'id'          => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'token_id'    => ['type' => 'BIGINT', 'unsigned' => true],
            'scanned_at'  => ['type' => 'DATETIME'],
            'ip_address'  => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent'  => ['type' => 'TEXT', 'null' => true],
            'metadata'    => ['type' => 'LONGTEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('token_id');
        $this->forge->createTable('qr_scans', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('qr_scans', true);
        $this->forge->dropTable('qr_tokens', true);
    }
}
