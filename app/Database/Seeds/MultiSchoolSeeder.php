<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * MultiSchoolSeeder - Create 5 diverse schools across Kenya.
 */
class MultiSchoolSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // Check if schools already exist
        $existing = $db->table('schools')->countAllResults();
        if ($existing > 0) {
            echo "ℹ️  Schools already exist, skipping school creation\n";
            return;
        }

        $schools = [
            [
                'school_code'             => 'NPS001',
                'school_name'             => 'Nairobi Primary School',
                'school_type'             => 'primary',
                'country'                 => 'Kenya',
                'county'                  => 'Nairobi',
                'sub_county'              => 'Westlands',
                'address'                 => 'P.O. Box 12345, Nairobi',
                'phone'                   => '+254712345001',
                'email'                   => 'info@nairobiprimary.ac.ke',
                'website'                 => 'https://nairobiprimary.ac.ke',
                'timezone'                => 'Africa/Nairobi',
                'currency'                => 'KES',
                'academic_year_start'     => '2025-01-01',
                'academic_year_end'       => '2025-12-31',
                'subscription_plan'       => 'premium',
                'subscription_expires_at' => '2026-12-31 23:59:59',
                'max_students'            => 500,
                'max_teachers'            => 30,
                'is_active'               => true,
                'created_at'              => date('Y-m-d H:i:s'),
                'updated_at'              => date('Y-m-d H:i:s'),
            ],
            [
                'school_code'             => 'MSS001',
                'school_name'             => 'Mombasa Secondary School',
                'school_type'             => 'secondary',
                'country'                 => 'Kenya',
                'county'                  => 'Mombasa',
                'sub_county'              => 'Mvita',
                'address'                 => 'P.O. Box 54321, Mombasa',
                'phone'                   => '+254712345002',
                'email'                   => 'admin@mombasasecondary.ac.ke',
                'website'                 => 'https://mombasasecondary.ac.ke',
                'timezone'                => 'Africa/Nairobi',
                'currency'                => 'KES',
                'academic_year_start'     => '2025-01-01',
                'academic_year_end'       => '2025-12-31',
                'subscription_plan'       => 'enterprise',
                'subscription_expires_at' => '2026-12-31 23:59:59',
                'max_students'            => 800,
                'max_teachers'            => 50,
                'is_active'               => true,
                'created_at'              => date('Y-m-d H:i:s'),
                'updated_at'              => date('Y-m-d H:i:s'),
            ],
            [
                'school_code'             => 'KMA001',
                'school_name'             => 'Kisumu Mixed Academy',
                'school_type'             => 'mixed',
                'country'                 => 'Kenya',
                'county'                  => 'Kisumu',
                'sub_county'              => 'Kisumu Central',
                'address'                 => 'P.O. Box 67890, Kisumu',
                'phone'                   => '+254712345003',
                'email'                   => 'hello@kisumumixed.ac.ke',
                'website'                 => 'https://kisumumixed.ac.ke',
                'timezone'                => 'Africa/Nairobi',
                'currency'                => 'KES',
                'academic_year_start'     => '2025-01-01',
                'academic_year_end'       => '2025-12-31',
                'subscription_plan'       => 'basic',
                'subscription_expires_at' => '2026-06-30 23:59:59',
                'max_students'            => 600,
                'max_teachers'            => 40,
                'is_active'               => true,
                'created_at'              => date('Y-m-d H:i:s'),
                'updated_at'              => date('Y-m-d H:i:s'),
            ],
            [
                'school_code'             => 'ETC001',
                'school_name'             => 'Eldoret Technical College',
                'school_type'             => 'college',
                'country'                 => 'Kenya',
                'county'                  => 'Uasin Gishu',
                'sub_county'              => 'Eldoret East',
                'address'                 => 'P.O. Box 98765, Eldoret',
                'phone'                   => '+254712345004',
                'email'                   => 'registrar@eldorettech.ac.ke',
                'website'                 => 'https://eldorettech.ac.ke',
                'timezone'                => 'Africa/Nairobi',
                'currency'                => 'KES',
                'academic_year_start'     => '2025-01-01',
                'academic_year_end'       => '2025-12-31',
                'subscription_plan'       => 'enterprise',
                'subscription_expires_at' => '2027-12-31 23:59:59',
                'max_students'            => 1000,
                'max_teachers'            => 60,
                'is_active'               => true,
                'created_at'              => date('Y-m-d H:i:s'),
                'updated_at'              => date('Y-m-d H:i:s'),
            ],
            [
                'school_code'             => 'NKS001',
                'school_name'             => 'Nakuru Kids School',
                'school_type'             => 'primary',
                'country'                 => 'Kenya',
                'county'                  => 'Nakuru',
                'sub_county'              => 'Nakuru East',
                'address'                 => 'P.O. Box 11223, Nakuru',
                'phone'                   => '+254712345005',
                'email'                   => 'contact@nakurukids.ac.ke',
                'website'                 => 'https://nakurukids.ac.ke',
                'timezone'                => 'Africa/Nairobi',
                'currency'                => 'KES',
                'academic_year_start'     => '2025-01-01',
                'academic_year_end'       => '2025-12-31',
                'subscription_plan'       => 'free',
                'subscription_expires_at' => null,
                'max_students'            => 300,
                'max_teachers'            => 20,
                'is_active'               => true,
                'created_at'              => date('Y-m-d H:i:s'),
                'updated_at'              => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($schools as $school) {
            $this->db->table('schools')->insert($school);
        }

        echo "✅ Created 5 schools:\n";
        echo "   - Nairobi Primary School (NPS001) - Premium\n";
        echo "   - Mombasa Secondary School (MSS001) - Enterprise\n";
        echo "   - Kisumu Mixed Academy (KMA001) - Basic\n";
        echo "   - Eldoret Technical College (ETC001) - Enterprise\n";
        echo "   - Nakuru Kids School (NKS001) - Free\n";
    }
}
