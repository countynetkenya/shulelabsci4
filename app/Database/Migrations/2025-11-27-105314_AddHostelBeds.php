<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHostelBeds extends Migration
{
    public function up()
    {
        // Create hostel_beds table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'room_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'bed_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['available', 'occupied', 'maintenance'],
                'default'    => 'available',
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
        $this->forge->addForeignKey('room_id', 'hostel_rooms', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addUniqueKey(['room_id', 'bed_number']);
        $this->forge->createTable('hostel_beds', true); // true = IF NOT EXISTS

        // Add bed_id to hostel_allocations if not exists
        $fields = $this->db->getFieldData('hostel_allocations');
        $hasBedId = false;
        foreach ($fields as $field) {
            if ($field->name === 'bed_id') {
                $hasBedId = true;
                break;
            }
        }

        if (!$hasBedId) {
            $this->forge->addColumn('hostel_allocations', [
                'bed_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'room_id',
                ],
            ]);
            // SQLite FK addition is tricky on existing tables, usually requires copy/rebuild.
            // We will skip adding the FK constraint strictly here to avoid SQLite complexity
            // or just rely on the column.
            // $this->forge->addForeignKey('bed_id', 'hostel_beds', 'id', 'CASCADE', 'CASCADE');
        }
    }

    public function down()
    {
        // SQLite doesn't support dropping FKs easily without table recreation,
        // but for this context we'll just drop the column which is supported in newer SQLite or ignore
        // In standard CI4/SQLite, dropping column might require table rebuild.
        // For now, let's just try dropping the table and column.

        $this->forge->dropColumn('hostel_allocations', 'bed_id');
        $this->forge->dropTable('hostel_beds');
    }
}
