<?php

namespace App\Modules\Admissions\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * AdmissionsSeeder - Seed sample admissions applications.
 */
class AdmissionsSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // Sample applications for school_id = 1
        $applications = [
            [
                'school_id' => 1,
                'application_number' => 'APP-1-2024-0001',
                'academic_year' => '2024',
                'term' => '1',
                'class_applied' => 7,
                'student_first_name' => 'John',
                'student_last_name' => 'Doe',
                'student_dob' => '2012-05-15',
                'student_gender' => 'male',
                'previous_school' => 'ABC Primary School',
                'parent_first_name' => 'Robert',
                'parent_last_name' => 'Doe',
                'parent_email' => 'robert.doe@example.com',
                'parent_phone' => '+254712345678',
                'parent_relationship' => 'father',
                'address' => '123 Main Street, Nairobi',
                'status' => 'submitted',
                'application_fee_paid' => 1,
                'fee_payment_ref' => 'PAY-2024-001',
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
            ],
            [
                'school_id' => 1,
                'application_number' => 'APP-1-2024-0002',
                'academic_year' => '2024',
                'term' => '1',
                'class_applied' => 8,
                'student_first_name' => 'Jane',
                'student_last_name' => 'Smith',
                'student_dob' => '2011-08-22',
                'student_gender' => 'female',
                'previous_school' => 'XYZ Academy',
                'parent_first_name' => 'Mary',
                'parent_last_name' => 'Smith',
                'parent_email' => 'mary.smith@example.com',
                'parent_phone' => '+254723456789',
                'parent_relationship' => 'mother',
                'address' => '456 Oak Avenue, Nairobi',
                'status' => 'under_review',
                'reviewed_by' => 1,
                'reviewed_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'application_fee_paid' => 1,
                'fee_payment_ref' => 'PAY-2024-002',
                'created_at' => date('Y-m-d H:i:s', strtotime('-7 days')),
            ],
            [
                'school_id' => 1,
                'application_number' => 'APP-1-2024-0003',
                'academic_year' => '2024',
                'term' => '1',
                'class_applied' => 9,
                'student_first_name' => 'Michael',
                'student_last_name' => 'Johnson',
                'student_dob' => '2010-03-10',
                'student_gender' => 'male',
                'previous_school' => 'Elite Secondary',
                'parent_first_name' => 'James',
                'parent_last_name' => 'Johnson',
                'parent_email' => 'james.johnson@example.com',
                'parent_phone' => '+254734567890',
                'parent_relationship' => 'father',
                'address' => '789 Pine Road, Nairobi',
                'status' => 'interview_scheduled',
                'reviewed_by' => 1,
                'reviewed_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'application_fee_paid' => 1,
                'fee_payment_ref' => 'PAY-2024-003',
                'created_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
            ],
            [
                'school_id' => 1,
                'application_number' => 'APP-1-2024-0004',
                'academic_year' => '2024',
                'term' => '2',
                'class_applied' => 7,
                'student_first_name' => 'Sarah',
                'student_last_name' => 'Williams',
                'student_dob' => '2012-11-05',
                'student_gender' => 'female',
                'previous_school' => 'Sunshine Primary',
                'parent_first_name' => 'Patricia',
                'parent_last_name' => 'Williams',
                'parent_email' => 'patricia.williams@example.com',
                'parent_phone' => '+254745678901',
                'parent_relationship' => 'mother',
                'address' => '321 Cedar Lane, Nairobi',
                'status' => 'accepted',
                'reviewed_by' => 1,
                'reviewed_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'decision_notes' => 'Excellent interview performance. Strong academic record.',
                'application_fee_paid' => 1,
                'fee_payment_ref' => 'PAY-2024-004',
                'created_at' => date('Y-m-d H:i:s', strtotime('-12 days')),
            ],
            [
                'school_id' => 1,
                'application_number' => 'APP-1-2024-0005',
                'academic_year' => '2024',
                'term' => '2',
                'class_applied' => 8,
                'student_first_name' => 'David',
                'student_last_name' => 'Brown',
                'student_dob' => '2011-01-20',
                'student_gender' => 'male',
                'previous_school' => 'Green Valley School',
                'parent_first_name' => 'Linda',
                'parent_last_name' => 'Brown',
                'parent_email' => 'linda.brown@example.com',
                'parent_phone' => '+254756789012',
                'parent_relationship' => 'guardian',
                'address' => '654 Maple Street, Nairobi',
                'status' => 'waitlisted',
                'reviewed_by' => 1,
                'reviewed_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'decision_notes' => 'Good candidate. Placed on waiting list due to capacity.',
                'application_fee_paid' => 1,
                'fee_payment_ref' => 'PAY-2024-005',
                'created_at' => date('Y-m-d H:i:s', strtotime('-8 days')),
            ],
        ];

        // Insert applications
        foreach ($applications as $application) {
            $db->table('applications')->insert($application);
        }

        echo 'Inserted ' . count($applications) . " sample applications.\n";
    }
}
