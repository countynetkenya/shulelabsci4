<?php

namespace App\Modules\Inventory\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'school_id' => 1,
                'category_id' => 1,
                'name' => 'School Uniform - Shirt (S)',
                'sku' => 'UNIF-S-001',
                'type' => 'physical',
                'unit_cost' => 500.00,
                'reorder_level' => 20,
                'description' => 'Standard white school shirt, size Small',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'school_id' => 1,
                'category_id' => 1,
                'name' => 'School Uniform - Shirt (M)',
                'sku' => 'UNIF-M-002',
                'type' => 'physical',
                'unit_cost' => 550.00,
                'reorder_level' => 20,
                'description' => 'Standard white school shirt, size Medium',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'school_id' => 1,
                'category_id' => 2,
                'name' => 'Mathematics Textbook Grade 4',
                'sku' => 'BOOK-MATH-G4',
                'type' => 'physical',
                'unit_cost' => 1200.00,
                'reorder_level' => 10,
                'description' => 'Primary Mathematics, Grade 4 Edition',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'school_id' => 1,
                'category_id' => 3,
                'name' => 'Transport Service - Zone A',
                'sku' => 'TRANS-ZA',
                'type' => 'service',
                'unit_cost' => 5000.00,
                'reorder_level' => 0,
                'description' => 'Monthly transport fee for Zone A',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'school_id' => 1,
                'category_id' => 4,
                'name' => 'Admission Bundle',
                'sku' => 'BUN-ADM-001',
                'type' => 'bundle',
                'unit_cost' => 15000.00,
                'reorder_level' => 0,
                'description' => 'Complete admission package including uniform and books',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Using query builder to ignore validation for seeding speed/simplicity
        $this->db->table('inventory_items')->insertBatch($data);
    }
}
