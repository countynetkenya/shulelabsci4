<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InventoryV2Seeder extends Seeder
{
    public function run()
    {
        // Create Locations
        $this->db->table('inventory_locations')->insert([
            'name' => 'Warehouse',
            'description' => 'Main Warehouse',
            'is_default' => true,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $warehouseId = $this->db->insertID();

        $this->db->table('inventory_locations')->insert([
            'name' => 'Shop',
            'description' => 'School Shop',
            'is_default' => false,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $shopId = $this->db->insertID();

        // Create Category
        $this->db->table('inventory_categories')->insert([
            'name' => 'Books',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $categoryId = $this->db->insertID();

        // Create Item
        $this->db->query('INSERT INTO "inventory_items" (category_id, name, sku, type, unit_cost, is_billable, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)', [
            $categoryId, 'Math Book', 'MATH-001', 'physical', 10.00, 1, date('Y-m-d H:i:s'),
        ]);
        $itemId = $this->db->insertID();

        // Create Stock in Warehouse
        $this->db->disableForeignKeyChecks();
        $this->db->table('inventory_stock')->insert([
            'item_id' => $itemId,
            'location_id' => $warehouseId,
            'quantity' => 100,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->db->enableForeignKeyChecks();

        // Create User for testing
        if ($this->db->table('users')->where('email', 'test@example.com')->countAllResults() == 0) {
            $this->db->table('users')->insert([
               'username' => 'testuser',
               'email' => 'test@example.com',
               'password_hash' => 'hash',
               'full_name' => 'Test User',
               'is_active' => 1,
               'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
