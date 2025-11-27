<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpgradeInventoryV2 extends Migration
{
    public function up()
    {
        $this->db->disableForeignKeyChecks();

        // 1. Create inventory_locations table
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'description' => ['type' => 'TEXT', 'null' => true],
            'is_default' => ['type' => 'BOOLEAN', 'default' => false],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('inventory_locations', true);

        // 2. Create inventory_stock table
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'item_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'location_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'quantity' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['item_id', 'location_id']);
        $this->forge->addForeignKey('item_id', 'inventory_items', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('location_id', 'inventory_locations', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('inventory_stock', true);

        // 3. Seeding: Insert a default "Main Store" location
        $this->db->table('inventory_locations')->insert([
            'name' => 'Main Store',
            'description' => 'Default central inventory location',
            'is_default' => true,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $mainStoreId = $this->db->insertID();

        // 4. Data Migration: Move quantity from inventory_items to inventory_stock
        $items = $this->db->table('inventory_items')->get()->getResult();
        $stockData = [];
        foreach ($items as $item) {
            if ($item->quantity > 0) {
                $stockData[] = [
                    'item_id' => $item->id,
                    'location_id' => $mainStoreId,
                    'quantity' => $item->quantity,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
        }
        if (!empty($stockData)) {
            $this->db->table('inventory_stock')->insertBatch($stockData);
        }

        // 5. Enhancement: Recreate inventory_items
        // We use explicit table recreation to be safe across all drivers (especially SQLite) when modifying columns and types.

        // Create new table with desired schema
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'category_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'sku' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'description' => ['type' => 'TEXT', 'null' => true],
            'type' => ['type' => 'ENUM', 'constraint' => ['physical', 'service', 'bundle'], 'default' => 'physical'],
            'unit_cost' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'reorder_level' => ['type' => 'INT', 'constraint' => 11, 'default' => 10],
            'location' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'is_billable' => ['type' => 'BOOLEAN', 'default' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('sku');
        $this->forge->addForeignKey('category_id', 'inventory_categories', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('inventory_items_v2', true);

        // Copy data
        $this->db->query("INSERT INTO inventory_items_v2 (id, category_id, name, sku, description, type, unit_cost, reorder_level, location, is_billable, created_at, updated_at)
                          SELECT id, category_id, name, sku, description, 'physical', unit_cost, reorder_level, location, 1, created_at, updated_at FROM inventory_items");

        // Drop old table
        $this->forge->dropTable('inventory_items');

        // Rename new table
        $this->forge->renameTable('inventory_items_v2', 'inventory_items');

        // 6. Create inventory_bundles table (Moved here)
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'bundle_item_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'component_item_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'quantity' => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('bundle_item_id', 'inventory_items', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('component_item_id', 'inventory_items', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('inventory_bundles', true);

        // 7. Create inventory_transfers table (Moved here)
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'item_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'from_location_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'to_location_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'quantity' => ['type' => 'INT', 'constraint' => 11],
            'status' => ['type' => 'ENUM', 'constraint' => ['pending', 'completed', 'cancelled'], 'default' => 'pending'],
            'initiated_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'completed_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'thread_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true], // For digital handshake
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'completed_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('item_id', 'inventory_items', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('from_location_id', 'inventory_locations', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('to_location_id', 'inventory_locations', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('initiated_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('completed_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('inventory_transfers', true);

        $this->db->enableForeignKeyChecks();
    }

    public function down()
    {
        // Reverse the process

        // 1. Revert inventory_items changes
        // Add quantity back
        $this->forge->addColumn('inventory_items', [
            'quantity' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
        ]);

        // Restore quantity from stock (summing up from all locations for simplicity, though data loss of location info is inevitable in down)
        $stocks = $this->db->table('inventory_stock')->get()->getResult();
        foreach ($stocks as $stock) {
            $this->db->query('UPDATE inventory_items SET quantity = quantity + ? WHERE id = ?', [$stock->quantity, $stock->item_id]);
        }

        // Revert type column
        $this->forge->addColumn('inventory_items', [
            'old_type' => ['type' => 'ENUM', 'constraint' => ['consumable', 'asset'], 'default' => 'consumable'],
        ]);
        $this->db->table('inventory_items')->update(['old_type' => 'consumable']); // Default fallback
        $this->forge->dropColumn('inventory_items', 'type');
        $this->forge->modifyColumn('inventory_items', [
            'old_type' => ['name' => 'type', 'type' => 'ENUM', 'constraint' => ['consumable', 'asset'], 'default' => 'consumable'],
        ]);
        $this->forge->dropColumn('inventory_items', 'is_billable');

        // 2. Drop tables
        $this->forge->dropTable('inventory_transfers', true);
        $this->forge->dropTable('inventory_bundles', true);
        $this->forge->dropTable('inventory_stock', true);
        $this->forge->dropTable('inventory_locations', true);
    }
}
