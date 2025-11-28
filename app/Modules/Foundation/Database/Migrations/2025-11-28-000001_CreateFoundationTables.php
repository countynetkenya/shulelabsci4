<?php

namespace App\Modules\Foundation\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates additional Foundation tables: tenant_contexts, qr_codes, ledger_accounts, ledger_journals, ledger_entries.
 */
class CreateFoundationTables extends Migration
{
    public function up(): void
    {
        // tenant_contexts - Active tenant sessions
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'session_id' => ['type' => 'VARCHAR', 'constraint' => 128],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'resolved_via' => ['type' => 'ENUM', 'constraint' => ['subdomain', 'session', 'token', 'header']],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'expires_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('session_id', false, false, 'idx_session');
        $this->forge->addKey('expires_at', false, false, 'idx_expires');
        $this->forge->createTable('tenant_contexts', true);

        // audit_seals - Cryptographic seals for audit batches (if not exists)
        if (!$this->db->tableExists('audit_seals')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'seal_date' => ['type' => 'DATE'],
                'first_event_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
                'last_event_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
                'event_count' => ['type' => 'INT', 'constraint' => 11],
                'hash_algorithm' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'SHA-256'],
                'data_hash' => ['type' => 'VARCHAR', 'constraint' => 64],
                'previous_seal_hash' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
                'sealed_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'sealed_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['school_id', 'seal_date'], 'uk_school_date');
            $this->forge->createTable('audit_seals', true);
        }

        // qr_codes - Generated QR codes
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'code' => ['type' => 'VARCHAR', 'constraint' => 100],
            'entity_type' => ['type' => 'VARCHAR', 'constraint' => 100],
            'entity_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'qr_type' => ['type' => 'ENUM', 'constraint' => ['id', 'url', 'data'], 'default' => 'id'],
            'payload' => ['type' => 'JSON', 'null' => true],
            'image_path' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'scanned_count' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'last_scanned_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'expires_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code', 'uk_code');
        $this->forge->addKey(['entity_type', 'entity_id'], false, false, 'idx_entity');
        $this->forge->createTable('qr_codes', true);

        // ledger_accounts - Chart of accounts
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'account_code' => ['type' => 'VARCHAR', 'constraint' => 20],
            'name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'account_type' => ['type' => 'ENUM', 'constraint' => ['asset', 'liability', 'equity', 'revenue', 'expense']],
            'parent_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'is_header' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'normal_balance' => ['type' => 'ENUM', 'constraint' => ['debit', 'credit']],
            'current_balance' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['school_id', 'account_code'], 'uk_school_code');
        $this->forge->createTable('ledger_accounts', true);

        // ledger_journals - Journal entry headers
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'journal_number' => ['type' => 'VARCHAR', 'constraint' => 50],
            'journal_date' => ['type' => 'DATE'],
            'description' => ['type' => 'VARCHAR', 'constraint' => 500],
            'reference_type' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'reference_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'total_amount' => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'status' => ['type' => 'ENUM', 'constraint' => ['draft', 'posted', 'reversed'], 'default' => 'draft'],
            'posted_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'posted_at' => ['type' => 'DATETIME', 'null' => true],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['school_id', 'journal_number'], 'uk_school_number');
        $this->forge->addKey('journal_date', false, false, 'idx_date');
        $this->forge->addKey(['reference_type', 'reference_id'], false, false, 'idx_reference');
        $this->forge->createTable('ledger_journals', true);

        // ledger_entries - Journal entry lines
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'journal_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'account_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'debit_amount' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'credit_amount' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'description' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('journal_id', false, false, 'idx_journal');
        $this->forge->addKey('account_id', false, false, 'idx_account');
        $this->forge->createTable('ledger_entries', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ledger_entries', true);
        $this->forge->dropTable('ledger_journals', true);
        $this->forge->dropTable('ledger_accounts', true);
        $this->forge->dropTable('qr_codes', true);
        $this->forge->dropTable('audit_seals', true);
        $this->forge->dropTable('tenant_contexts', true);
    }
}
