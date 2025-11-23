<?php

namespace Modules\Foundation\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLedgerTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'              => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'transaction_key' => ['type' => 'VARCHAR', 'constraint' => 191],
            'tenant_id'       => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'currency_code'   => ['type' => 'CHAR', 'constraint' => 3],
            'transacted_at'   => ['type' => 'DATETIME'],
            'metadata_json'   => ['type' => 'LONGTEXT', 'null' => true],
            'created_at'      => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('transaction_key', false, true);
        $this->forge->addKey('transacted_at');
        $this->forge->createTable('ledger_transactions', true);

        $this->forge->addField([
            'id'             => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'transaction_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'account_code'   => ['type' => 'VARCHAR', 'constraint' => 100],
            'direction'      => ['type' => 'ENUM', 'constraint' => ['debit', 'credit']],
            'amount'         => ['type' => 'DECIMAL', 'constraint' => '18,4'],
            'memo'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'     => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('transaction_id');
        $this->forge->createTable('ledger_entries', true);

        $this->forge->addField([
            'id'           => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'    => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'period_start' => ['type' => 'DATE'],
            'period_end'   => ['type' => 'DATE'],
            'locked_at'    => ['type' => 'DATETIME'],
            'locked_by'    => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'period_start', 'period_end']);
        $this->forge->createTable('ledger_period_locks', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ledger_entries', true);
        $this->forge->dropTable('ledger_transactions', true);
        $this->forge->dropTable('ledger_period_locks', true);
    }
}
