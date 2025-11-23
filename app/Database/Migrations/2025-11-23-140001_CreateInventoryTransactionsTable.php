<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInventoryTransactionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INTEGER',
                'auto_increment' => true,
            ],
            'school_id' => [
                'type' => 'INT',
                'null' => false,
            ],
            'asset_id' => [
                'type' => 'INT',
                'null' => false,
            ],
            'transaction_type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
            ],
            'quantity' => [
                'type' => 'INT',
                'null' => false,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'transaction_date' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addKey('school_id');
        $this->forge->addKey('asset_id');
        $this->forge->createTable('inventory_transactions');
    }

    public function down()
    {
        $this->forge->dropTable('inventory_transactions');
    }
}
