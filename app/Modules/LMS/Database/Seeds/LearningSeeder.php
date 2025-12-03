<?php

namespace Modules\LMS\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LearningSeeder extends Seeder
{
    public function run()
    {
        // Ensure we have a school
        $school = $this->db->table('schools')->get()->getRow();
        if (!$school) {
            $this->db->table('schools')->insert([
                'school_name' => 'Learning Test School',
                'school_code' => 'LMS001',
                'is_active'   => 1,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
            $schoolId = $this->db->insertID();
        } else {
            $schoolId = $school->id;
        }

        // Ensure we have a teacher
        $teacher = $this->db->table('users')->where('email', 'teacher@test.com')->get()->getRow();
        if (!$teacher) {
            $this->db->table('users')->insert([
                'username'      => 'teacher',
                'email'         => 'teacher@test.com',
                'password_hash' => password_hash('password', PASSWORD_DEFAULT),
                'full_name'     => 'Mr. Teacher',
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
            $teacherId = $this->db->insertID();
        } else {
            $teacherId = $teacher->id;
        }

        // Create a Course
        $this->db->table('learning_courses')->insert([
            'school_id'   => $schoolId,
            'teacher_id'  => $teacherId,
            'title'       => 'Introduction to Algebra',
            'description' => 'Basic algebra concepts.',
            'status'      => 'published',
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
        $courseId = $this->db->insertID();

        // Create Lessons
        $lessons = [
            ['title' => 'Variables', 'content' => 'Introduction to variables.', 'sequence_order' => 1],
            ['title' => 'Equations', 'content' => 'Solving simple equations.', 'sequence_order' => 2],
            ['title' => 'Functions', 'content' => 'Understanding functions.', 'sequence_order' => 3],
        ];

        foreach ($lessons as $lesson) {
            $this->db->table('learning_lessons')->insert([
                'course_id'      => $courseId,
                'title'          => $lesson['title'],
                'content'        => $lesson['content'],
                'sequence_order' => $lesson['sequence_order'],
                'created_at'     => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
