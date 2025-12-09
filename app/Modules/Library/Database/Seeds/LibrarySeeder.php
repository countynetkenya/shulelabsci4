<?php

namespace App\Modules\Library\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * LibrarySeeder - Seeds library_books table with realistic sample data
 * 
 * Run with: php spark db:seed App\Modules\Library\Database\Seeds\LibrarySeeder
 */
class LibrarySeeder extends Seeder
{
    public function run(): void
    {
        // Use school_id = 1 for test data (adjust as needed)
        $schoolId = 1;

        $books = [
            [
                'school_id'        => $schoolId,
                'title'            => 'To Kill a Mockingbird',
                'author'           => 'Harper Lee',
                'isbn'             => '978-0-06-112008-4',
                'category'         => 'Fiction',
                'total_copies'     => 5,
                'available_copies' => 3,
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'        => $schoolId,
                'title'            => 'A Brief History of Time',
                'author'           => 'Stephen Hawking',
                'isbn'             => '978-0-553-38016-3',
                'category'         => 'Science',
                'total_copies'     => 3,
                'available_copies' => 2,
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'        => $schoolId,
                'title'            => 'The Great Gatsby',
                'author'           => 'F. Scott Fitzgerald',
                'isbn'             => '978-0-7432-7356-5',
                'category'         => 'Fiction',
                'total_copies'     => 4,
                'available_copies' => 4,
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'        => $schoolId,
                'title'            => 'Introduction to Algorithms',
                'author'           => 'Thomas H. Cormen',
                'isbn'             => '978-0-262-03384-8',
                'category'         => 'Computer Science',
                'total_copies'     => 2,
                'available_copies' => 1,
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'        => $schoolId,
                'title'            => 'The Kenya We Want',
                'author'           => 'Ngugi wa Thiong\'o',
                'isbn'             => '978-9966-47-123-4',
                'category'         => 'African Literature',
                'total_copies'     => 6,
                'available_copies' => 5,
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ],
        ];

        // Check if data already exists to avoid duplicates
        $existing = $this->db->table('library_books')
            ->where('school_id', $schoolId)
            ->countAllResults();

        if ($existing === 0) {
            $this->db->table('library_books')->insertBatch($books);
            echo "Seeded " . count($books) . " library books for school_id = {$schoolId}\n";
        } else {
            echo "Library books already exist for school_id = {$schoolId}. Skipping.\n";
        }
    }
}
