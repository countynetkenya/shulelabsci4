<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFeeStructuresTable extends Migration
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
            'grade_level' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
            ],
            'fee_items' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addKey(['school_id', 'grade_level']);
        $this->forge->createTable('fee_structures');
    }

    public function down()
    {
        $this->forge->dropTable('fee_structures');
    }
}
