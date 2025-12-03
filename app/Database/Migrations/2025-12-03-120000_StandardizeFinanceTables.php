<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class StandardizeFinanceTables extends Migration
{
    public function up()
    {
        // Drop legacy/conflicting tables
        $this->forge->dropTable('invoices', true);
        $this->forge->dropTable('finance_payments', true);
        $this->forge->dropTable('finance_invoices', true);
        $this->forge->dropTable('finance_fee_structures', true);

        // 1. Fee Structures
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'amount' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'term' => ['type' => 'VARCHAR', 'constraint' => 50],
            'academic_year' => ['type' => 'VARCHAR', 'constraint' => 9],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('school_id');
        $this->forge->addForeignKey('school_id', 'schools', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('finance_fee_structures');

        // 2. Invoices
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'student_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'fee_structure_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'reference_number' => ['type' => 'VARCHAR', 'constraint' => 50],
            'amount' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'balance' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'status' => ['type' => 'ENUM', 'constraint' => ['unpaid', 'partial', 'paid', 'overdue'], 'default' => 'unpaid'],
            'due_date' => ['type' => 'DATE'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('school_id');
        $this->forge->addKey('student_id');
        $this->forge->addForeignKey('school_id', 'schools', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('student_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('fee_structure_id', 'finance_fee_structures', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('finance_invoices');

        // 3. Payments
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'invoice_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'amount' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'method' => ['type' => 'ENUM', 'constraint' => ['cash', 'bank_transfer', 'mobile_money', 'cheque']],
            'reference_code' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'paid_at' => ['type' => 'DATETIME'],
            'recorded_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('school_id');
        $this->forge->addKey('invoice_id');
        $this->forge->addForeignKey('school_id', 'schools', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('invoice_id', 'finance_invoices', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('recorded_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('finance_payments');
    }

    public function down()
    {
        $this->forge->dropTable('finance_payments', true);
        $this->forge->dropTable('finance_invoices', true);
        $this->forge->dropTable('finance_fee_structures', true);
    }
}
