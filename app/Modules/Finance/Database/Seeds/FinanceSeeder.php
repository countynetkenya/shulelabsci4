<?php

namespace Modules\Finance\Database\Seeds;

use CodeIgniter\Database\Seeder;

class FinanceSeeder extends Seeder
{
    public function run()
    {
        $schoolId = 1; // Default test school ID

        // 1. Create Fee Structures
        $feeStructures = [
            [
                'school_id'     => $schoolId,
                'name'          => 'Term 1 2025 Tuition Fee',
                'amount'        => 15000.00,
                'term'          => 'Term 1',
                'academic_year' => '2024/2025',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'     => $schoolId,
                'name'          => 'Term 1 2025 Transport Fee',
                'amount'        => 5000.00,
                'term'          => 'Term 1',
                'academic_year' => '2024/2025',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'     => $schoolId,
                'name'          => 'Term 1 2025 Lunch Program',
                'amount'        => 3000.00,
                'term'          => 'Term 1',
                'academic_year' => '2024/2025',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('finance_fee_structures')->insertBatch($feeStructures);

        // 2. Create Invoices
        $invoices = [
            [
                'school_id'         => $schoolId,
                'student_id'        => 1,
                'fee_structure_id'  => 1,
                'reference_number'  => 'INV-2025-001',
                'amount'            => 15000.00,
                'balance'           => 5000.00, // Partially paid
                'status'            => 'partial',
                'due_date'          => '2025-02-28',
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'         => $schoolId,
                'student_id'        => 1,
                'fee_structure_id'  => 2,
                'reference_number'  => 'INV-2025-002',
                'amount'            => 5000.00,
                'balance'           => 5000.00,
                'status'            => 'unpaid',
                'due_date'          => '2025-02-28',
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'         => $schoolId,
                'student_id'        => 2,
                'fee_structure_id'  => 1,
                'reference_number'  => 'INV-2025-003',
                'amount'            => 15000.00,
                'balance'           => 0.00,
                'status'            => 'paid',
                'due_date'          => '2025-02-28',
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('finance_invoices')->insertBatch($invoices);

        // 3. Create Payments (Transactions)
        $payments = [
            [
                'school_id'      => $schoolId,
                'invoice_id'     => 1,
                'amount'         => 10000.00,
                'method'         => 'mobile_money',
                'reference_code' => 'MPE-QWE123RTY',
                'paid_at'        => '2025-01-15 10:30:00',
                'recorded_by'    => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'      => $schoolId,
                'invoice_id'     => 3,
                'amount'         => 15000.00,
                'method'         => 'bank_transfer',
                'reference_code' => 'BNK-TXN-456789',
                'paid_at'        => '2025-01-10 14:20:00',
                'recorded_by'    => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'      => $schoolId,
                'invoice_id'     => 1,
                'amount'         => 5000.00,
                'method'         => 'cash',
                'reference_code' => null,
                'paid_at'        => '2025-02-01 09:15:00',
                'recorded_by'    => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'      => $schoolId,
                'invoice_id'     => 2,
                'amount'         => 2500.00,
                'method'         => 'cheque',
                'reference_code' => 'CHQ-001234',
                'paid_at'        => '2025-01-20 11:00:00',
                'recorded_by'    => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'      => $schoolId,
                'invoice_id'     => 2,
                'amount'         => 2500.00,
                'method'         => 'mobile_money',
                'reference_code' => 'MPE-XYZ789ABC',
                'paid_at'        => '2025-01-25 16:45:00',
                'recorded_by'    => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('finance_payments')->insertBatch($payments);
    }
}
