<?php

namespace App\Modules\Learning\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LearningSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'school_id'   => 1,
                'teacher_id'  => 1,
                'title'       => 'Introduction to Mathematics',
                'description' => 'Basic mathematics covering algebra, geometry, and trigonometry',
                'status'      => 'published',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'   => 1,
                'teacher_id'  => 1,
                'title'       => 'English Literature',
                'description' => 'Study of classic and modern English literature',
                'status'      => 'published',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'   => 1,
                'teacher_id'  => 1,
                'title'       => 'Physics Fundamentals',
                'description' => 'Introduction to physics concepts and principles',
                'status'      => 'published',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'   => 1,
                'teacher_id'  => 1,
                'title'       => 'World History',
                'description' => 'Comprehensive study of world historical events',
                'status'      => 'published',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'   => 1,
                'teacher_id'  => 1,
                'title'       => 'Computer Science Basics',
                'description' => 'Introduction to programming and computer systems',
                'status'      => 'draft',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('learning_courses')->insertBatch($data);
    }
}
