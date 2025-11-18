<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCi4SessionsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'       => 'VARCHAR',
                'constraint' => 128,
                'null'       => false,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => false,
                'default'    => '0',
            ],
            'timestamp' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
            ],
            'data' => [
                'type' => 'MEDIUMBLOB',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('timestamp');

        $this->forge->createTable('ci4_sessions', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ci4_sessions', true);
    }
}
