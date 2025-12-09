<?php

namespace Tests\Feature\Finance;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

class FinanceWebTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;
    use TenantTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
    }

    public function testCanViewFinanceDashboard()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('/finance');

        $result->assertOK();
        $result->assertSee('Finance Dashboard');
    }

    public function testCanCreateFeeStructure()
    {
        $data = [
            'name' => 'Term 1 Tuition',
            'amount' => 15000.00,
            'term' => 'Term 1',
            'academic_year' => '2025',
        ];

        $result = $this->withSession($this->getAdminSession())
                       ->post('/finance/fee-structures', $data);

        $result->assertRedirect();
        $this->seeInDatabase('finance_fee_structures', [
            'name' => 'Term 1 Tuition',
            'amount' => 15000.00,
            'school_id' => $this->schoolId,
        ]);
    }

    public function testCanCreateInvoice()
    {
        // Create a student
        $this->db->table('users')->insert([
            'username' => 'student1',
            'email' => 'student1@test.com',
            'password_hash' => password_hash('password', PASSWORD_DEFAULT),
            'full_name' => 'Student One',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $studentId = $this->db->insertID();

        $data = [
            'student_id' => $studentId,
            'amount' => 5000.00,
            'due_date' => date('Y-m-d', strtotime('+30 days')),
        ];

        $result = $this->withSession($this->getAdminSession())
                       ->post('/finance/invoices', $data);

        $result->assertRedirect();

        $this->seeInDatabase('finance_invoices', [
            'student_id' => $studentId,
            'amount' => 5000.00,
            'balance' => 5000.00,
            'status' => 'unpaid',
            'school_id' => $this->schoolId,
        ]);
    }

    public function testCanRecordPayment()
    {
        // 1. Create Student
        $this->db->table('users')->insert([
            'username' => 'student2',
            'email' => 'student2@test.com',
            'password_hash' => password_hash('password', PASSWORD_DEFAULT),
            'full_name' => 'Student Two',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $studentId = $this->db->insertID();

        // 2. Create Invoice
        $this->db->table('finance_invoices')->insert([
            'school_id' => $this->schoolId,
            'student_id' => $studentId,
            'reference_number' => 'INV-TEST-001',
            'amount' => 10000.00,
            'balance' => 10000.00,
            'status' => 'unpaid',
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $invoiceId = $this->db->insertID();

        // 3. Record Partial Payment
        $paymentData = [
            'invoice_id' => $invoiceId,
            'amount' => 4000.00,
            'method' => 'cash',
            'paid_at' => date('Y-m-d'),
        ];

        $result = $this->withSession($this->getAdminSession())
                       ->post('/finance/payments', $paymentData);

        $result->assertRedirect();

        // 4. Verify Payment Recorded
        $this->seeInDatabase('finance_payments', [
            'invoice_id' => $invoiceId,
            'amount' => 4000.00,
            'school_id' => $this->schoolId,
        ]);

        // 5. Verify Invoice Updated (Partial)
        $this->seeInDatabase('finance_invoices', [
            'id' => $invoiceId,
            'balance' => 6000.00,
            'status' => 'partial',
        ]);

        // 6. Record Remaining Payment
        $paymentData2 = [
            'invoice_id' => $invoiceId,
            'amount' => 6000.00,
            'method' => 'mobile_money',
            'paid_at' => date('Y-m-d'),
        ];

        $this->withSession($this->getAdminSession())
             ->post('/finance/payments', $paymentData2);

        // 7. Verify Invoice Updated (Paid)
        $this->seeInDatabase('finance_invoices', [
            'id' => $invoiceId,
            'balance' => 0.00,
            'status' => 'paid',
        ]);
    }
}
