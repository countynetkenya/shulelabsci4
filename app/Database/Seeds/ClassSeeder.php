<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * ClassSeeder - Create 30 classes across 5 schools.
 */
class ClassSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        echo "\n📚 Creating classes for multi-school system...\n";

        // School 1: Nairobi Primary (6 classes)
        $this->createClasses($db, 1, [
            ['class_name' => 'Grade 1', 'grade_level' => '1', 'section' => 'A', 'max_capacity' => 40, 'teacher_id' => 25],
            ['class_name' => 'Grade 2', 'grade_level' => '2', 'section' => 'A', 'max_capacity' => 40, 'teacher_id' => 26],
            ['class_name' => 'Grade 3', 'grade_level' => '3', 'section' => 'A', 'max_capacity' => 38, 'teacher_id' => 27],
            ['class_name' => 'Grade 4', 'grade_level' => '4', 'section' => 'A', 'max_capacity' => 38, 'teacher_id' => 28],
            ['class_name' => 'Grade 5', 'grade_level' => '5', 'section' => 'A', 'max_capacity' => 35, 'teacher_id' => 29],
            ['class_name' => 'Grade 6', 'grade_level' => '6', 'section' => 'A', 'max_capacity' => 35, 'teacher_id' => 30],
        ]);

        // School 2: Mombasa Secondary (8 classes)
        $this->createClasses($db, 2, [
            ['class_name' => 'Form 1 Stream A', 'grade_level' => 'Form 1', 'section' => 'A', 'max_capacity' => 45, 'teacher_id' => 141],
            ['class_name' => 'Form 1 Stream B', 'grade_level' => 'Form 1', 'section' => 'B', 'max_capacity' => 45, 'teacher_id' => 142],
            ['class_name' => 'Form 2 Stream A', 'grade_level' => 'Form 2', 'section' => 'A', 'max_capacity' => 45, 'teacher_id' => 143],
            ['class_name' => 'Form 2 Stream B', 'grade_level' => 'Form 2', 'section' => 'B', 'max_capacity' => 45, 'teacher_id' => 144],
            ['class_name' => 'Form 3 Stream A', 'grade_level' => 'Form 3', 'section' => 'A', 'max_capacity' => 40, 'teacher_id' => 145],
            ['class_name' => 'Form 3 Stream B', 'grade_level' => 'Form 3', 'section' => 'B', 'max_capacity' => 40, 'teacher_id' => 146],
            ['class_name' => 'Form 4 Stream A', 'grade_level' => 'Form 4', 'section' => 'A', 'max_capacity' => 40, 'teacher_id' => 147],
            ['class_name' => 'Form 4 Stream B', 'grade_level' => 'Form 4', 'section' => 'B', 'max_capacity' => 40, 'teacher_id' => 148],
        ]);

        // School 3: Kisumu Mixed (7 classes)
        $this->createClasses($db, 3, [
            ['class_name' => 'Grade 4', 'grade_level' => '4', 'section' => 'A', 'max_capacity' => 30, 'teacher_id' => 191],
            ['class_name' => 'Grade 5', 'grade_level' => '5', 'section' => 'A', 'max_capacity' => 30, 'teacher_id' => 192],
            ['class_name' => 'Grade 6', 'grade_level' => '6', 'section' => 'A', 'max_capacity' => 30, 'teacher_id' => 193],
            ['class_name' => 'Form 1', 'grade_level' => 'Form 1', 'section' => 'A', 'max_capacity' => 35, 'teacher_id' => 194],
            ['class_name' => 'Form 2', 'grade_level' => 'Form 2', 'section' => 'A', 'max_capacity' => 35, 'teacher_id' => 195],
            ['class_name' => 'Form 3', 'grade_level' => 'Form 3', 'section' => 'A', 'max_capacity' => 35, 'teacher_id' => 196],
            ['class_name' => 'Form 4', 'grade_level' => 'Form 4', 'section' => 'A', 'max_capacity' => 30, 'teacher_id' => 197],
        ]);

        // School 4: Eldoret Technical (5 classes)
        $this->createClasses($db, 4, [
            ['class_name' => 'Year 1 Engineering', 'grade_level' => 'Year 1', 'section' => 'Engineering', 'max_capacity' => 50, 'teacher_id' => 227],
            ['class_name' => 'Year 2 Engineering', 'grade_level' => 'Year 2', 'section' => 'Engineering', 'max_capacity' => 50, 'teacher_id' => 228],
            ['class_name' => 'Year 3 Engineering', 'grade_level' => 'Year 3', 'section' => 'Engineering', 'max_capacity' => 45, 'teacher_id' => 229],
            ['class_name' => 'Year 4 Engineering', 'grade_level' => 'Year 4', 'section' => 'Engineering', 'max_capacity' => 45, 'teacher_id' => 230],
            ['class_name' => 'Certificate Course', 'grade_level' => 'Certificate', 'section' => 'General', 'max_capacity' => 60, 'teacher_id' => 231],
        ]);

        // School 5: Nakuru Kids (4 classes)
        $this->createClasses($db, 5, [
            ['class_name' => 'Pre-Kindergarten', 'grade_level' => 'Pre-K', 'section' => 'A', 'max_capacity' => 20, 'teacher_id' => 271],
            ['class_name' => 'Kindergarten', 'grade_level' => 'K', 'section' => 'A', 'max_capacity' => 25, 'teacher_id' => 272],
            ['class_name' => 'Grade 1', 'grade_level' => '1', 'section' => 'A', 'max_capacity' => 30, 'teacher_id' => 273],
            ['class_name' => 'Grade 2', 'grade_level' => '2', 'section' => 'A', 'max_capacity' => 30, 'teacher_id' => 274],
        ]);

        echo "\n✅ Created 30 classes across 5 schools\n";
    }

    private function createClasses($db, int $schoolId, array $classes): void
    {
        $names = [1 => 'Nairobi Primary', 2 => 'Mombasa Secondary', 3 => 'Kisumu Mixed', 4 => 'Eldoret Technical', 5 => 'Nakuru Kids'];
        $created = 0;

        foreach ($classes as $class) {
            $existing = $db->table('school_classes')->where('school_id', $schoolId)->where('class_name', $class['class_name'])->get()->getRow();
            if ($existing) {
                continue;
            }

            $db->table('school_classes')->insert([
                'school_id' => $schoolId,
                'class_name' => $class['class_name'],
                'grade_level' => $class['grade_level'],
                'section' => $class['section'],
                'class_teacher_id' => $class['teacher_id'],
                'max_capacity' => $class['max_capacity'],
                'academic_year' => '2025',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $created++;
        }

        if ($created > 0) {
            echo "   ✓ {$names[$schoolId]}: {$created} classes\n";
        }
    }
}
