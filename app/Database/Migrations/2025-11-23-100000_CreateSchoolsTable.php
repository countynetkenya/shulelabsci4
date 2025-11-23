<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSchoolsTable extends Migration
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
            'school_code' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'unique' => true,
            ],
            'school_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'school_type' => [
                'type' => 'ENUM',
                'constraint' => ['primary', 'secondary', 'mixed', 'college'],
                'default' => 'mixed',
            ],
            'country' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'default' => 'Kenya',
            ],
            'county' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'sub_county' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'phone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'website' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'logo_url' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'timezone' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'Africa/Nairobi',
            ],
            'currency' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'default' => 'KES',
            ],
            'academic_year_start' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'academic_year_end' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'subscription_plan' => [
                'type' => 'ENUM',
                'constraint' => ['free', 'basic', 'premium', 'enterprise'],
                'default' => 'free',
            ],
            'subscription_expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'max_students' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 100,
            ],
            'max_teachers' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 10,
            ],
            'is_active' => [
                'type' => 'BOOLEAN',
                'default' => true,
            ],
            'settings' => [
                'type' => 'JSON',
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
        $this->forge->addUniqueKey('school_code');

        $attributes = [];
        if ($this->db->DBDriver === 'MySQLi') {
            $attributes = ['ENGINE' => 'InnoDB'];
        }

        $this->forge->createTable('schools', false, $attributes);
    }

    public function down()
    {
        $this->forge->dropTable('schools', true);
    }
}
