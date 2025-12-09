<?php

namespace App\Modules\Teacher\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TeacherSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'school_id'       => 1,
                'teacher_id'      => null,
                'first_name'      => 'Robert',
                'last_name'       => 'Anderson',
                'employee_id'     => 'TEACH001',
                'department'      => 'Science',
                'subjects'        => 'Physics, Chemistry',
                'qualification'   => 'MSc Physics',
                'date_of_joining' => '2020-01-15',
                'status'          => 'active',
                'phone'           => '+254711223344',
                'email'           => 'robert.anderson@example.com',
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'       => 1,
                'teacher_id'      => null,
                'first_name'      => 'Patricia',
                'last_name'       => 'Williams',
                'employee_id'     => 'TEACH002',
                'department'      => 'Mathematics',
                'subjects'        => 'Algebra, Geometry',
                'qualification'   => 'BSc Mathematics',
                'date_of_joining' => '2019-09-01',
                'status'          => 'active',
                'phone'           => '+254722334455',
                'email'           => 'patricia.williams@example.com',
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'       => 1,
                'teacher_id'      => null,
                'first_name'      => 'Michael',
                'last_name'       => 'Brown',
                'employee_id'     => 'TEACH003',
                'department'      => 'Languages',
                'subjects'        => 'English, Literature',
                'qualification'   => 'MA English',
                'date_of_joining' => '2021-03-20',
                'status'          => 'active',
                'phone'           => '+254733445566',
                'email'           => 'michael.brown@example.com',
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'       => 1,
                'teacher_id'      => null,
                'first_name'      => 'Jennifer',
                'last_name'       => 'Davis',
                'employee_id'     => 'TEACH004',
                'department'      => 'Arts',
                'subjects'        => 'Music, Drama',
                'qualification'   => 'BFA',
                'date_of_joining' => '2018-08-15',
                'status'          => 'active',
                'phone'           => '+254744556677',
                'email'           => 'jennifer.davis@example.com',
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'       => 1,
                'teacher_id'      => null,
                'first_name'      => 'William',
                'last_name'       => 'Martinez',
                'employee_id'     => 'TEACH005',
                'department'      => 'Physical Education',
                'subjects'        => 'Sports, PE',
                'qualification'   => 'BEd Physical Education',
                'date_of_joining' => '2022-01-10',
                'status'          => 'active',
                'phone'           => '+254755667788',
                'email'           => 'william.martinez@example.com',
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('teachers')->insertBatch($data);
    }
}
