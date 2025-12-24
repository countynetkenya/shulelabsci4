<?php

namespace Modules\LMS\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * LMSSeeder - Seed sample LMS course records
 */
class LMSSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'school_id' => 1,
                'teacher_id' => 1,
                'instructor_id' => 1,
                'title' => 'Introduction to Mathematics',
                'description' => 'A comprehensive course covering fundamental mathematical concepts including algebra, geometry, and calculus.',
                'modules' => 'Algebra\nGeometry\nTrigonometry\nCalculus',
                'status' => 'published',
                'created_at' => '2025-12-01 10:00:00',
                'updated_at' => '2025-12-01 10:00:00',
            ],
            [
                'school_id' => 1,
                'teacher_id' => 1,
                'instructor_id' => 1,
                'title' => 'Advanced Physics',
                'description' => 'Explore the principles of physics including mechanics, thermodynamics, and electromagnetism.',
                'modules' => 'Mechanics\nThermodynamics\nElectromagnetism\nQuantum Physics',
                'status' => 'published',
                'created_at' => '2025-12-02 10:00:00',
                'updated_at' => '2025-12-02 10:00:00',
            ],
            [
                'school_id' => 1,
                'teacher_id' => 1,
                'instructor_id' => 1,
                'title' => 'English Literature',
                'description' => 'Study classic and modern literature with focus on critical analysis and writing skills.',
                'modules' => 'Poetry Analysis\nNovel Studies\nDrama\nEssay Writing',
                'status' => 'draft',
                'created_at' => '2025-12-03 10:00:00',
                'updated_at' => '2025-12-03 10:00:00',
            ],
            [
                'school_id' => 1,
                'teacher_id' => 1,
                'instructor_id' => 1,
                'title' => 'Computer Science Basics',
                'description' => 'Learn programming fundamentals, algorithms, and data structures.',
                'modules' => 'Programming Basics\nAlgorithms\nData Structures\nWeb Development',
                'status' => 'published',
                'created_at' => '2025-12-04 10:00:00',
                'updated_at' => '2025-12-04 10:00:00',
            ],
            [
                'school_id' => 1,
                'teacher_id' => 1,
                'instructor_id' => 1,
                'title' => 'World History',
                'description' => 'Journey through major historical events and civilizations from ancient to modern times.',
                'modules' => 'Ancient Civilizations\nMedieval Period\nModern History\nContemporary Events',
                'status' => 'archived',
                'created_at' => '2025-11-15 10:00:00',
                'updated_at' => '2025-11-30 10:00:00',
            ],
        ];

        $this->db->table('learning_courses')->insertBatch($data);
    }
}
