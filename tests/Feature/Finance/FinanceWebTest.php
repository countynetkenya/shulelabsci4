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
}
