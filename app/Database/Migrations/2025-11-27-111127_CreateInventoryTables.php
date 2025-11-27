<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInventoryTables extends Migration
{
    public function up()
    {
        // 1. Inventory Categories
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'description' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('inventory_categories', true);

        // 2. Inventory Suppliers
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'contact_person' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'phone' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'email' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'address' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('inventory_suppliers', true);

        // 3. Inventory Items
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'category_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'sku' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'description' => ['type' => 'TEXT', 'null' => true],
            'type' => ['type' => 'ENUM', 'constraint' => ['consumable', 'asset'], 'default' => 'consumable'],
            'quantity' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'unit_cost' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'reorder_level' => ['type' => 'INT', 'constraint' => 11, 'default' => 10],
            'location' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('sku');
        $this->forge->addForeignKey('category_id', 'inventory_categories', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('inventory_items', true);

        // 4. Inventory Transactions
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'item_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'recipient_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'supplier_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'type' => ['type' => 'ENUM', 'constraint' => ['receive', 'issue', 'adjustment', 'return']],
            'quantity' => ['type' => 'INT', 'constraint' => 11],
            'unit_price' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('item_id', 'inventory_items', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('recipient_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('supplier_id', 'inventory_suppliers', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('inventory_transactions', true);
    }

    public function down()
    {
        $this->forge->dropTable('inventory_transactions', true);
        $this->forge->dropTable('inventory_items', true);
        $this->forge->dropTable('inventory_suppliers', true);
        $this->forge->dropTable('inventory_categories', true);
    }
}
