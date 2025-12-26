<?php

namespace Modules\POS\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * PosSeeder - Seeds sample POS products for testing.
 */
class PosSeeder extends Seeder
{
    /**
     * Run the seeder to insert sample POS products.
     *
     * @return void
     */
    public function run(): void
    {
        // Clear existing data
        $this->db->table('pos_products')->truncate();

        $data = [
            [
                'school_id' => 1,
                'name' => 'School Uniform - Shirt',
                'description' => 'Official school uniform shirt - white with logo',
                'price' => 500.00,
                'stock' => 50,
                'sku' => 'UNI-SHI-001',
                'barcode' => '1234567890001',
                'category' => 'Uniforms',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'school_id' => 1,
                'name' => 'School Bag',
                'description' => 'Durable school backpack with school emblem',
                'price' => 1200.00,
                'stock' => 30,
                'sku' => 'ACC-BAG-001',
                'barcode' => '1234567890002',
                'category' => 'Accessories',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'school_id' => 1,
                'name' => 'Mathematics Textbook',
                'description' => 'Grade 10 Mathematics textbook (KICD approved)',
                'price' => 800.00,
                'stock' => 100,
                'sku' => 'BK-MATH-G10',
                'barcode' => '1234567890003',
                'category' => 'Textbooks',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'school_id' => 1,
                'name' => 'Exercise Book - 40 Pages',
                'description' => 'Standard 40-page exercise book',
                'price' => 50.00,
                'stock' => 500,
                'sku' => 'STAT-EX40',
                'barcode' => '1234567890004',
                'category' => 'Stationery',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'school_id' => 1,
                'name' => 'Scientific Calculator',
                'description' => 'Casio fx-82MS scientific calculator',
                'price' => 1500.00,
                'stock' => 20,
                'sku' => 'CALC-SCI-001',
                'barcode' => '1234567890005',
                'category' => 'Electronics',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('pos_products')->insertBatch($data);

        echo 'Inserted ' . count($data) . " POS products.\n";
    }
}
