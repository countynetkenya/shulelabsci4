<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLibraryBorrowingsTable extends Migration
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
            'book_id' => [
                'type' => 'INT',
                'null' => false,
            ],
            'student_id' => [
                'type' => 'INT',
                'null' => false,
            ],
            'borrowed_date' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'due_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'returned_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'borrowed',
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
        $this->forge->addKey('book_id');
        $this->forge->addKey('student_id');
        $this->forge->createTable('library_borrowings');
    }

    public function down()
    {
        $this->forge->dropTable('library_borrowings');
    }
}
