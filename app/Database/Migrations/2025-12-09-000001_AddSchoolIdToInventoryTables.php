<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSchoolIdToInventoryTables extends Migration
{
    public function up()
    {
        $tables = [
            'inventory_categories',
            'inventory_suppliers',
            'inventory_items',
            'inventory_locations',
            'inventory_stock',
            'inventory_transactions'
        ];

        foreach ($tables as $table) {
            if (!$this->db->fieldExists('school_id', $table)) {
                $this->forge->addColumn($table, [
                    'school_id' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'unsigned' => true,
                        'null' => true, // Nullable for existing data, strictly should be NOT NULL
                        'after' => 'id'
                    ]
                ]);
                // Ideally add index
                // $this->db->query("CREATE INDEX idx_{$table}_school_id ON {$table}(school_id)");
            }
        }
    }

    public function down()
    {
        $tables = [
            'inventory_categories',
            'inventory_suppliers',
            'inventory_items',
            'inventory_locations',
            'inventory_stock',
            'inventory_transactions'
        ];

        foreach ($tables as $table) {
            if ($this->db->fieldExists('school_id', $table)) {
                $this->forge->dropColumn($table, 'school_id');
            }
        }
    }
}
