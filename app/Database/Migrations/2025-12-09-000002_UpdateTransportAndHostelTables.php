<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateTransportAndHostelTables extends Migration
{
    public function up()
    {
        // Transport
        $fields = [
            'driver_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'capacity'
            ]
        ];
        $this->forge->addColumn('transport_vehicles', $fields);

        // Hostel
        $hostelFields = [
            'school_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'id'
            ],
            'type' => [
                'type' => 'ENUM',
                'constraint' => ['dorm', 'private'],
                'default' => 'dorm',
                'after' => 'capacity'
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['available', 'full', 'maintenance'],
                'default' => 'available',
                'after' => 'type'
            ]
        ];
        $this->forge->addColumn('hostel_rooms', $hostelFields);
    }

    public function down()
    {
        $this->forge->dropColumn('transport_vehicles', 'driver_name');
        $this->forge->dropColumn('hostel_rooms', ['school_id', 'type', 'status']);
    }
}
