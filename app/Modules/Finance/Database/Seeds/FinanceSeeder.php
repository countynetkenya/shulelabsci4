<?php

namespace Modules\Finance\Database\Seeds;

use CodeIgniter\Database\Seeder;

class FinanceSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Fee Structures
        $feeStructures = [
            [
                'name' => 'Term 1 2025 Tuition',
                'amount' => 15000.00,
                'academic_period_id' => 1,
                'class_id' => 1, // Grade 1
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Term 1 2025 Transport',
                'amount' => 5000.00,
                'academic_period_id' => 1,
                'class_id' => null, // All classes
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Term 1 2025 Lunch',
                'amount' => 3000.00,
                'academic_period_id' => 1,
                'class_id' => null,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('finance_fee_structures')->insertBatch($feeStructures);

        // 2. Create Invoices (for Student ID 1)
        $invoices = [
            [
                'student_id' => 1,
                'fee_structure_id' => 1, // Tuition
                'amount' => 15000.00,
                'balance' => 5000.00, // Partially paid
                'status' => 'partial',
                'due_date' => '2025-01-31',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'student_id' => 1,
                'fee_structure_id' => 2, // Transport
                'amount' => 5000.00,
                'balance' => 5000.00,
                'status' => 'unpaid',
                'due_date' => '2025-01-31',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('finance_invoices')->insertBatch($invoices);

        // 3. Create Payments (for Student ID 1)
        $payments = [
            [
                'student_id' => 1,
                'invoice_id' => 1, // Paying Tuition
                'amount' => 10000.00,
                'method' => 'M-Pesa',
                'reference_number' => 'QWE123RTY',
                'recorded_by' => 1, // Admin
                'transaction_date' => '2025-01-15',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('finance_payments')->insertBatch($payments);
    }
}
