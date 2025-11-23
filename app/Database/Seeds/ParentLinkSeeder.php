<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * ParentLinkSeeder - Link parents to their children in student_enrollments.
 *
 * Strategy:
 * - Each parent gets 1-3 children from their school
 * - Uses student_metadata JSON field to store parent-child relationships
 * - Creates realistic family structures
 */
class ParentLinkSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        
        echo "\n👨‍👩‍👧‍👦 Linking parents to students...\n";

        // School 1: 5 parents (134-138) → 25 students (33-57)
        $this->linkParentsToStudents($db, 1, range(134, 138), range(33, 57));

        // School 2: 5 parents (183-187) → 30 students (153-182)
        $this->linkParentsToStudents($db, 2, range(183, 187), range(153, 182));

        // School 3: 3 parents (221-223) → 20 students (201-220)
        $this->linkParentsToStudents($db, 3, range(221, 223), range(201, 220));

        // School 4: 2 parents (267-268) → 25 students (242-266)
        $this->linkParentsToStudents($db, 4, range(267, 268), range(242, 266));

        // School 5: 4 parents (291-294) → 15 students (276-290)
        $this->linkParentsToStudents($db, 5, range(291, 294), range(276, 290));

        echo "\n✅ Linked 19 parents to their children\n";
    }

    private function linkParentsToStudents($db, int $schoolId, array $parentIds, array $studentIds): void
    {
        $schoolNames = [
            1 => 'Nairobi Primary',
            2 => 'Mombasa Secondary',
            3 => 'Kisumu Mixed',
            4 => 'Eldoret Technical',
            5 => 'Nakuru Kids',
        ];

        $linked = 0;
        $studentIndex = 0;
        $totalStudents = count($studentIds);

        foreach ($parentIds as $parentId) {
            // Each parent gets 1-3 children (randomly distributed)
            $childrenCount = min(
                rand(1, 3),
                $totalStudents - $studentIndex
            );

            $children = [];
            for ($i = 0; $i < $childrenCount; $i++) {
                if ($studentIndex >= $totalStudents) {
                    break;
                }
                $children[] = $studentIds[$studentIndex];
                $studentIndex++;
            }

            if (empty($children)) {
                continue;
            }

            // Update each student enrollment with parent info
            foreach ($children as $studentId) {
                $enrollment = $db->table('student_enrollments')
                    ->where('school_id', $schoolId)
                    ->where('student_id', $studentId)
                    ->get()
                    ->getRow();

                if ($enrollment) {
                    // Get existing metadata or create new
                    $metadata = $enrollment->student_metadata 
                        ? json_decode($enrollment->student_metadata, true) 
                        : [];

                    // Add parent ID (support multiple parents)
                    if (!isset($metadata['parent_ids'])) {
                        $metadata['parent_ids'] = [];
                    }
                    
                    if (!in_array($parentId, $metadata['parent_ids'])) {
                        $metadata['parent_ids'][] = $parentId;
                    }

                    // Update enrollment
                    $db->table('student_enrollments')
                        ->where('id', $enrollment->id)
                        ->update([
                            'student_metadata' => json_encode($metadata),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);

                    $linked++;
                }
            }
        }

        echo "   ✓ {$schoolNames[$schoolId]}: {$linked} parent-student links\n";
    }
}
