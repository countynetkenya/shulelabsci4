<?php

namespace Modules\Transport\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TransportSeeder extends Seeder
{
    public function run()
    {
        $schoolId = 1; // Default school for testing

        // Seed Transport Vehicles
        $vehicles = [
            [
                'school_id' => $schoolId,
                'registration_number' => 'KCA 123A',
                'make' => 'Toyota',
                'model' => 'Coaster',
                'year' => 2020,
                'capacity' => 36,
                'fuel_type' => 'diesel',
                'insurance_expiry' => date('Y-m-d', strtotime('+180 days')),
                'fitness_expiry' => date('Y-m-d', strtotime('+90 days')),
                'status' => 'active',
                'gps_device_id' => 'GPS001',
                'driver_name' => 'John Kamau',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'school_id' => $schoolId,
                'registration_number' => 'KCB 456B',
                'make' => 'Nissan',
                'model' => 'Civilian',
                'year' => 2019,
                'capacity' => 28,
                'fuel_type' => 'diesel',
                'insurance_expiry' => date('Y-m-d', strtotime('+150 days')),
                'fitness_expiry' => date('Y-m-d', strtotime('+120 days')),
                'status' => 'active',
                'gps_device_id' => 'GPS002',
                'driver_name' => 'Peter Mwangi',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'school_id' => $schoolId,
                'registration_number' => 'KCC 789C',
                'make' => 'Isuzu',
                'model' => 'NPR',
                'year' => 2021,
                'capacity' => 40,
                'fuel_type' => 'diesel',
                'insurance_expiry' => date('Y-m-d', strtotime('+200 days')),
                'fitness_expiry' => date('Y-m-d', strtotime('+150 days')),
                'status' => 'active',
                'gps_device_id' => 'GPS003',
                'driver_name' => 'Samuel Ochieng',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'school_id' => $schoolId,
                'registration_number' => 'KCD 012D',
                'make' => 'Toyota',
                'model' => 'Hiace',
                'year' => 2018,
                'capacity' => 14,
                'fuel_type' => 'petrol',
                'insurance_expiry' => date('Y-m-d', strtotime('+90 days')),
                'fitness_expiry' => date('Y-m-d', strtotime('+60 days')),
                'status' => 'maintenance',
                'gps_device_id' => 'GPS004',
                'driver_name' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];
        $this->db->table('transport_vehicles')->insertBatch($vehicles);

        // Seed Transport Routes
        $routes = [
            [
                'school_id' => $schoolId,
                'name' => 'Westlands Route',
                'code' => 'WR01',
                'description' => 'Covers Westlands, Parklands, and Highridge areas',
                'vehicle_id' => 1,
                'driver_id' => 1,
                'assistant_id' => null,
                'distance_km' => 15.5,
                'estimated_duration_min' => 45,
                'monthly_fee' => 3500.00,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'school_id' => $schoolId,
                'name' => 'Eastlands Route',
                'code' => 'ER01',
                'description' => 'Covers Umoja, Donholm, and Buruburu areas',
                'vehicle_id' => 2,
                'driver_id' => 2,
                'assistant_id' => null,
                'distance_km' => 22.0,
                'estimated_duration_min' => 60,
                'monthly_fee' => 4000.00,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'school_id' => $schoolId,
                'name' => 'South B Route',
                'code' => 'SR01',
                'description' => 'Covers South B, South C, and Lang\'ata areas',
                'vehicle_id' => 3,
                'driver_id' => 3,
                'assistant_id' => null,
                'distance_km' => 18.0,
                'estimated_duration_min' => 50,
                'monthly_fee' => 3800.00,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];
        $this->db->table('transport_routes')->insertBatch($routes);
    }
}
