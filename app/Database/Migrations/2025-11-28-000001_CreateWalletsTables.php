<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates Wallets module tables.
 */
class CreateWalletsTables extends Migration
{
    public function up(): void
    {
        // wallets - Wallet accounts
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'wallet_type' => ['type' => 'ENUM', 'constraint' => ['student', 'parent', 'staff']],
            'balance' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'currency' => ['type' => 'VARCHAR', 'constraint' => 3, 'default' => 'KES'],
            'status' => ['type' => 'ENUM', 'constraint' => ['active', 'suspended', 'closed'], 'default' => 'active'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['school_id', 'user_id'], 'uk_school_user');
        $this->forge->createTable('wallets', true);

        // wallet_transactions - Transaction history
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'wallet_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'transaction_ref' => ['type' => 'VARCHAR', 'constraint' => 50],
            'transaction_type' => ['type' => 'ENUM', 'constraint' => ['credit', 'debit']],
            'amount' => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'balance_before' => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'balance_after' => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'category' => ['type' => 'VARCHAR', 'constraint' => 50],
            'description' => ['type' => 'VARCHAR', 'constraint' => 255],
            'reference_type' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'reference_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['pending', 'completed', 'failed', 'reversed'], 'default' => 'completed'],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('transaction_ref', 'uk_transaction_ref');
        $this->forge->addKey(['wallet_id', 'created_at'], false, false, 'idx_wallet_date');
        $this->forge->addKey(['reference_type', 'reference_id'], false, false, 'idx_reference');
        $this->forge->createTable('wallet_transactions', true);

        // wallet_topups - Top-up requests
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'wallet_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'amount' => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'payment_method' => ['type' => 'ENUM', 'constraint' => ['mpesa', 'bank', 'cash', 'card']],
            'payment_reference' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['pending', 'completed', 'failed', 'cancelled'], 'default' => 'pending'],
            'completed_at' => ['type' => 'DATETIME', 'null' => true],
            'processed_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['wallet_id', 'status'], false, false, 'idx_wallet_status');
        $this->forge->createTable('wallet_topups', true);

        // wallet_transfers - Wallet-to-wallet transfers
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'from_wallet_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'to_wallet_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'amount' => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'description' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['pending', 'completed', 'failed', 'reversed'], 'default' => 'pending'],
            'initiated_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'completed_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('from_wallet_id', false, false, 'idx_from_wallet');
        $this->forge->addKey('to_wallet_id', false, false, 'idx_to_wallet');
        $this->forge->createTable('wallet_transfers', true);

        // wallet_limits - Spending limits
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'wallet_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'limit_type' => ['type' => 'ENUM', 'constraint' => ['daily', 'weekly', 'monthly', 'per_transaction']],
            'max_amount' => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'current_usage' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'reset_at' => ['type' => 'DATETIME', 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['wallet_id', 'limit_type'], 'uk_wallet_limit_type');
        $this->forge->createTable('wallet_limits', true);

        // wallet_auto_allocations - Auto-allocation rules
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'wallet_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'allocation_type' => ['type' => 'ENUM', 'constraint' => ['fee_payment', 'savings', 'specific_invoice']],
            'target_type' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'target_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'percentage' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'fixed_amount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true],
            'priority' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['wallet_id', 'priority'], false, false, 'idx_wallet_priority');
        $this->forge->createTable('wallet_auto_allocations', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('wallet_auto_allocations', true);
        $this->forge->dropTable('wallet_limits', true);
        $this->forge->dropTable('wallet_transfers', true);
        $this->forge->dropTable('wallet_topups', true);
        $this->forge->dropTable('wallet_transactions', true);
        $this->forge->dropTable('wallets', true);
    }
}
