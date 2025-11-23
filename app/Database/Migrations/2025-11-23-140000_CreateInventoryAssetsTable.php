<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInventoryAssetsTable extends Migration
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
            'asset_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'asset_code' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'category' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'quantity' => [
                'type' => 'INT',
                'default' => 0,
            ],
            'unit_price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'total_value' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
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
        $this->forge->addKey('asset_code');
        $this->forge->createTable('inventory_assets');
    }

    public function down()
    {
        $this->forge->dropTable('inventory_assets');
    }
}
