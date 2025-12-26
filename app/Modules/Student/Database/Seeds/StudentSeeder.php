<?php

namespace App\Modules\Student\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * StudentSeeder - Seeds sample student records.
 */
class StudentSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'school_id'        => 1,
                'student_id'       => null,
                'first_name'       => 'John',
                'last_name'        => 'Doe',
                'admission_number' => 'STU001',
                'date_of_birth'    => '2010-05-15',
                'gender'           => 'male',
                'status'           => 'active',
                'parent_name'      => 'Jane Doe',
                'parent_phone'     => '+254700123456',
                'parent_email'     => 'jane.doe@example.com',
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'        => 1,
                'student_id'       => null,
                'first_name'       => 'Sarah',
                'last_name'        => 'Smith',
                'admission_number' => 'STU002',
                'date_of_birth'    => '2011-08-22',
                'gender'           => 'female',
                'status'           => 'active',
                'parent_name'      => 'Michael Smith',
                'parent_phone'     => '+254700234567',
                'parent_email'     => 'michael.smith@example.com',
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'        => 1,
                'student_id'       => null,
                'first_name'       => 'David',
                'last_name'        => 'Johnson',
                'admission_number' => 'STU003',
                'date_of_birth'    => '2010-12-10',
                'gender'           => 'male',
                'status'           => 'active',
                'parent_name'      => 'Emily Johnson',
                'parent_phone'     => '+254700345678',
                'parent_email'     => 'emily.johnson@example.com',
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'        => 1,
                'student_id'       => null,
                'first_name'       => 'Maria',
                'last_name'        => 'Garcia',
                'admission_number' => 'STU004',
                'date_of_birth'    => '2009-03-18',
                'gender'           => 'female',
                'status'           => 'active',
                'parent_name'      => 'Carlos Garcia',
                'parent_phone'     => '+254700456789',
                'parent_email'     => 'carlos.garcia@example.com',
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'        => 1,
                'student_id'       => null,
                'first_name'       => 'James',
                'last_name'        => 'Wilson',
                'admission_number' => 'STU005',
                'date_of_birth'    => '2011-06-25',
                'gender'           => 'male',
                'status'           => 'active',
                'parent_name'      => 'Linda Wilson',
                'parent_phone'     => '+254700567890',
                'parent_email'     => 'linda.wilson@example.com',
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('students')->insertBatch($data);
    }
}
