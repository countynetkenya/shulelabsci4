<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSettingTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'settingID' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'schoolID' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'school_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'school_address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'school_phone' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'school_email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'school_logo' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'currency_symbol' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'default' => 'KSH',
            ],
            'timezone' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'Africa/Nairobi',
            ],
            'academic_year' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
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
        $this->forge->addKey('settingID');
        $this->forge->addKey('schoolID');
        $this->forge->createTable('setting');

        // Insert default setting for system
        $this->db->table('setting')->insert([
            'settingID' => 1,
            'schoolID' => null,
            'school_name' => 'ShuleLabs Platform',
            'school_address' => 'Nairobi, Kenya',
            'school_email' => 'admin@shulelabs.local',
            'currency_symbol' => 'KSH',
            'timezone' => 'Africa/Nairobi',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('setting');
    }
}
