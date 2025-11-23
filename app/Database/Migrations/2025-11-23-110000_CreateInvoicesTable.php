<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInvoicesTable extends Migration
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
            'student_id' => [
                'type' => 'INT',
                'null' => false,
            ],
            'total_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'paid_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'balance' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'due_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'pending',
            ],
            'items' => [
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
        $this->forge->addKey('school_id');
        $this->forge->addKey('student_id');
        $this->forge->createTable('invoices');
    }

    public function down()
    {
        $this->forge->dropTable('invoices');
    }
}
