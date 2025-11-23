<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateThreadAnnouncementsTable extends Migration
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
            'author_id' => [
                'type' => 'INT',
                'null' => false,
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'content' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'target_audience' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'all',
            ],
            'published_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
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
        $this->forge->createTable('thread_announcements');
    }

    public function down()
    {
        $this->forge->dropTable('thread_announcements');
    }
}
