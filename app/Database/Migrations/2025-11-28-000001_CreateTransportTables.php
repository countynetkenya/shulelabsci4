<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates Transport module tables.
 */
class CreateTransportTables extends Migration
{
    public function up(): void
    {
        // transport_vehicles - Fleet management
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'registration_number' => ['type' => 'VARCHAR', 'constraint' => 20],
            'make' => ['type' => 'VARCHAR', 'constraint' => 50],
            'model' => ['type' => 'VARCHAR', 'constraint' => 50],
            'year' => ['type' => 'YEAR', 'null' => true],
            'capacity' => ['type' => 'INT', 'constraint' => 11],
            'fuel_type' => ['type' => 'ENUM', 'constraint' => ['petrol', 'diesel', 'electric', 'hybrid'], 'default' => 'diesel'],
            'insurance_expiry' => ['type' => 'DATE', 'null' => true],
            'fitness_expiry' => ['type' => 'DATE', 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['active', 'maintenance', 'retired'], 'default' => 'active'],
            'gps_device_id' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['school_id', 'registration_number'], 'uk_school_reg');
        $this->forge->createTable('transport_vehicles', true);

        // transport_drivers - Driver management
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'phone' => ['type' => 'VARCHAR', 'constraint' => 20],
            'license_number' => ['type' => 'VARCHAR', 'constraint' => 50],
            'license_expiry' => ['type' => 'DATE'],
            'license_class' => ['type' => 'VARCHAR', 'constraint' => 10],
            'status' => ['type' => 'ENUM', 'constraint' => ['active', 'on_leave', 'suspended', 'terminated'], 'default' => 'active'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('school_id', false, false, 'idx_school');
        $this->forge->createTable('transport_drivers', true);

        // transport_routes - Route definitions
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'code' => ['type' => 'VARCHAR', 'constraint' => 20],
            'description' => ['type' => 'TEXT', 'null' => true],
            'vehicle_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'driver_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'assistant_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'distance_km' => ['type' => 'DECIMAL', 'constraint' => '6,2', 'null' => true],
            'estimated_duration_min' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'monthly_fee' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['school_id', 'code'], 'uk_school_code');
        $this->forge->createTable('transport_routes', true);

        // transport_stops - Route stops
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'route_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'sequence' => ['type' => 'INT', 'constraint' => 11],
            'pickup_time' => ['type' => 'TIME', 'null' => true],
            'dropoff_time' => ['type' => 'TIME', 'null' => true],
            'latitude' => ['type' => 'DECIMAL', 'constraint' => '10,8', 'null' => true],
            'longitude' => ['type' => 'DECIMAL', 'constraint' => '11,8', 'null' => true],
            'landmark' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['route_id', 'sequence'], false, false, 'idx_route_sequence');
        $this->forge->createTable('transport_stops', true);

        // transport_assignments - Student-route assignments
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'student_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'route_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'pickup_stop_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'dropoff_stop_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'academic_year' => ['type' => 'VARCHAR', 'constraint' => 20],
            'start_date' => ['type' => 'DATE'],
            'end_date' => ['type' => 'DATE', 'null' => true],
            'transport_type' => ['type' => 'ENUM', 'constraint' => ['both', 'pickup_only', 'dropoff_only'], 'default' => 'both'],
            'status' => ['type' => 'ENUM', 'constraint' => ['active', 'suspended', 'cancelled'], 'default' => 'active'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['student_id', 'academic_year'], false, false, 'idx_student_year');
        $this->forge->addKey(['route_id', 'status'], false, false, 'idx_route_status');
        $this->forge->createTable('transport_assignments', true);

        // transport_trips - Daily trip tracking
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'route_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'vehicle_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'driver_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'trip_date' => ['type' => 'DATE'],
            'trip_type' => ['type' => 'ENUM', 'constraint' => ['pickup', 'dropoff']],
            'scheduled_start' => ['type' => 'TIME'],
            'actual_start' => ['type' => 'DATETIME', 'null' => true],
            'actual_end' => ['type' => 'DATETIME', 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['scheduled', 'in_progress', 'completed', 'cancelled'], 'default' => 'scheduled'],
            'odometer_start' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'odometer_end' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['route_id', 'trip_date', 'trip_type'], false, false, 'idx_route_date_type');
        $this->forge->createTable('transport_trips', true);

        // transport_attendance - Student attendance on bus
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'trip_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'student_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'stop_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'boarded_at' => ['type' => 'DATETIME', 'null' => true],
            'alighted_at' => ['type' => 'DATETIME', 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['boarded', 'absent', 'alternate'], 'default' => 'boarded'],
            'notes' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['trip_id', 'student_id'], 'uk_trip_student');
        $this->forge->createTable('transport_attendance', true);

        // transport_gps_logs - GPS tracking data
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'vehicle_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'trip_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true],
            'latitude' => ['type' => 'DECIMAL', 'constraint' => '10,8'],
            'longitude' => ['type' => 'DECIMAL', 'constraint' => '11,8'],
            'speed' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'heading' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'recorded_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['vehicle_id', 'recorded_at'], false, false, 'idx_vehicle_time');
        $this->forge->addKey(['trip_id', 'recorded_at'], false, false, 'idx_trip_time');
        $this->forge->createTable('transport_gps_logs', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('transport_gps_logs', true);
        $this->forge->dropTable('transport_attendance', true);
        $this->forge->dropTable('transport_trips', true);
        $this->forge->dropTable('transport_assignments', true);
        $this->forge->dropTable('transport_stops', true);
        $this->forge->dropTable('transport_routes', true);
        $this->forge->dropTable('transport_drivers', true);
        $this->forge->dropTable('transport_vehicles', true);
    }
}
