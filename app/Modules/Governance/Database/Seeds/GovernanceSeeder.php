<?php

namespace App\Modules\Governance\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * GovernanceSeeder - Seed sample governance policies
 */
class GovernanceSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        
        // Sample policies for school_id = 1
        $policies = [
            [
                'school_id' => 1,
                'policy_number' => 'POL-1-ACA-001',
                'title' => 'Academic Integrity Policy',
                'category' => 'Academic',
                'content' => 'This policy outlines the school\'s commitment to maintaining the highest standards of academic integrity. All students are expected to submit original work and properly cite sources. Plagiarism, cheating, and other forms of academic dishonesty will not be tolerated and will result in disciplinary action.',
                'summary' => 'Policy governing academic honesty and integrity standards',
                'version' => '2.0',
                'status' => 'approved',
                'effective_date' => '2024-01-01',
                'review_date' => '2025-01-01',
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-60 days')),
            ],
            [
                'school_id' => 1,
                'policy_number' => 'POL-1-FIN-001',
                'title' => 'Fee Payment and Refund Policy',
                'category' => 'Financial',
                'content' => 'This policy establishes the procedures for fee payment, late payment penalties, and refund requests. Fees must be paid by the specified deadline each term. Late payments will incur a 5% penalty. Refund requests must be submitted in writing and will be evaluated on a case-by-case basis.',
                'summary' => 'Guidelines for fee collection, payments, and refunds',
                'version' => '1.5',
                'status' => 'approved',
                'effective_date' => '2024-01-01',
                'review_date' => '2024-12-31',
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-45 days')),
            ],
            [
                'school_id' => 1,
                'policy_number' => 'POL-1-HR-001',
                'title' => 'Staff Code of Conduct',
                'category' => 'HR',
                'content' => 'All staff members are expected to maintain professional conduct at all times. This includes treating students, parents, and colleagues with respect, maintaining confidentiality, arriving on time, and adhering to dress code requirements. Violations will be addressed through the progressive discipline process.',
                'summary' => 'Professional standards and expectations for all staff',
                'version' => '3.0',
                'status' => 'approved',
                'effective_date' => '2023-09-01',
                'review_date' => '2024-09-01',
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-90 days')),
            ],
            [
                'school_id' => 1,
                'policy_number' => 'POL-1-SAF-001',
                'title' => 'Student Safety and Wellbeing Policy',
                'category' => 'Safety',
                'content' => 'The school is committed to providing a safe and secure learning environment. This policy covers emergency procedures, visitor protocols, student supervision requirements, and reporting procedures for safety concerns. All staff must complete annual safety training.',
                'summary' => 'Comprehensive safety and security measures for students',
                'version' => '2.5',
                'status' => 'under_review',
                'effective_date' => '2024-02-01',
                'review_date' => '2025-02-01',
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-30 days')),
            ],
            [
                'school_id' => 1,
                'policy_number' => 'POL-1-IT-001',
                'title' => 'Technology Acceptable Use Policy',
                'category' => 'IT',
                'content' => 'This policy governs the use of school technology resources including computers, tablets, internet access, and learning management systems. Users must use technology responsibly, respect intellectual property, maintain password security, and report security concerns immediately. Misuse will result in loss of privileges.',
                'summary' => 'Guidelines for appropriate use of school technology',
                'version' => '1.0',
                'status' => 'draft',
                'effective_date' => null,
                'review_date' => null,
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-15 days')),
            ],
        ];

        // Insert policies
        foreach ($policies as $policy) {
            $db->table('policies')->insert($policy);
        }

        echo "Inserted " . count($policies) . " sample governance policies.\n";
    }
}
