<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateThreadMessagesTable extends Migration
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
            'sender_id' => [
                'type' => 'INT',
                'null' => false,
            ],
            'recipient_id' => [
                'type' => 'INT',
                'null' => false,
            ],
            'subject' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'body' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'sent_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'is_read' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'read_at' => [
                'type' => 'DATETIME',
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
        $this->forge->addKey('sender_id');
        $this->forge->addKey('recipient_id');
        $this->forge->createTable('thread_messages');
    }

    public function down()
    {
        $this->forge->dropTable('thread_messages');
    }
}
