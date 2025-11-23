<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * EnrollmentSeeder - Enroll 115 students into classes across 5 schools.
 *
 * Distribution:
 * - School 1: 25 students across 6 classes
 * - School 2: 30 students across 8 classes
 * - School 3: 20 students across 7 classes
 * - School 4: 25 students across 5 classes
 * - School 5: 15 students across 4 classes
 * Total: 115 student enrollments
 */
class EnrollmentSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        echo "\n📝 Enrolling students into classes...\n";

        // Get class IDs for each school
        $school1Classes = $this->getSchoolClasses($db, 1);
        $school2Classes = $this->getSchoolClasses($db, 2);
        $school3Classes = $this->getSchoolClasses($db, 3);
        $school4Classes = $this->getSchoolClasses($db, 4);
        $school5Classes = $this->getSchoolClasses($db, 5);

        // School 1: 25 students (IDs 33-57) distributed across 6 classes
        $this->enrollStudents($db, 1, range(33, 57), $school1Classes, [4, 5, 4, 4, 4, 4]);

        // School 2: 30 students (IDs 153-182) distributed across 8 classes
        $this->enrollStudents($db, 2, range(153, 182), $school2Classes, [4, 4, 4, 4, 4, 3, 4, 3]);

        // School 3: 20 students (IDs 201-220) distributed across 7 classes
        $this->enrollStudents($db, 3, range(201, 220), $school3Classes, [3, 3, 3, 3, 3, 3, 2]);

        // School 4: 25 students (IDs 242-266) distributed across 5 classes
        $this->enrollStudents($db, 4, range(242, 266), $school4Classes, [5, 5, 5, 5, 5]);

        // School 5: 15 students (IDs 276-290) distributed across 4 classes
        $this->enrollStudents($db, 5, range(276, 290), $school5Classes, [4, 4, 4, 3]);

        echo "\n✅ Enrolled 115 students across all schools\n";
    }

    private function getSchoolClasses($db, int $schoolId): array
    {
        return $db->table('school_classes')
            ->where('school_id', $schoolId)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function enrollStudents($db, int $schoolId, array $studentIds, array $classes, array $distribution): void
    {
        $schoolNames = [
            1 => 'Nairobi Primary',
            2 => 'Mombasa Secondary',
            3 => 'Kisumu Mixed',
            4 => 'Eldoret Technical',
            5 => 'Nakuru Kids',
        ];

        $enrolled = 0;
        $studentIndex = 0;

        foreach ($classes as $classIndex => $class) {
            $studentsInClass = $distribution[$classIndex] ?? 0;

            for ($i = 0; $i < $studentsInClass; $i++) {
                if ($studentIndex >= count($studentIds)) {
                    break 2; // Exit both loops
                }

                $studentId = $studentIds[$studentIndex];

                // Check if enrollment already exists
                $existing = $db->table('student_enrollments')
                    ->where('school_id', $schoolId)
                    ->where('student_id', $studentId)
                    ->where('class_id', $class['id'])
                    ->get()
                    ->getRow();

                if (!$existing) {
                    $data = [
                        'school_id'      => $schoolId,
                        'student_id'     => $studentId,
                        'class_id'       => $class['id'],
                        'enrollment_date' => date('Y-m-d'),
                        'status'         => 'active',
                        'created_at'     => date('Y-m-d H:i:s'),
                        'updated_at'     => date('Y-m-d H:i:s'),
                    ];

                    $db->table('student_enrollments')->insert($data);
                    $enrolled++;
                }

                $studentIndex++;
            }
        }

        echo "   ✓ {$schoolNames[$schoolId]}: {$enrolled} enrollments\n";
    }
}
