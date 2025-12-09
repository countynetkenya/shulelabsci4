<?php

namespace Tests\Feature\Finance;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

class FinanceApiTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;
    use TenantTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
    }

    public function testCanFetchInvoicesViaApi()
    {
        // 1. Create Student
        $this->db->table('users')->insert([
            'username' => 'student_api',
            'email' => 'student_api@test.com',
            'password_hash' => password_hash('password', PASSWORD_DEFAULT),
            'full_name' => 'Student API',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $studentId = $this->db->insertID();

        // 2. Create Invoice
        $this->db->table('finance_invoices')->insert([
            'school_id' => $this->schoolId,
            'student_id' => $studentId,
            'reference_number' => 'INV-API-001',
            'amount' => 5000.00,
            'balance' => 5000.00,
            'status' => 'unpaid',
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $invoiceId = $this->db->insertID();

        // 3. Call API
        $result = $this->withSession($this->getAdminSession())
                       ->get('/api/finance/invoices');

        // 4. Verify Response
        $result->assertOK();

        $json = json_decode($result->getJSON(), true);
        $this->assertArrayHasKey('data', $json);
        $this->assertNotEmpty($json['data']);

        // Find the invoice in the data
        $invoice = null;
        foreach ($json['data'] as $item) {
            if ($item['reference_number'] === 'INV-API-001') {
                $invoice = $item;
                break;
            }
        }

        $this->assertNotNull($invoice, 'Invoice not found in response');
        $this->assertEquals('5000.00', $invoice['amount']);
        $this->assertEquals('unpaid', $invoice['status']);
    }

    public function testCanFetchSingleInvoiceViaApi()
    {
        // 1. Create Student
        $this->db->table('users')->insert([
            'username' => 'student_api_2',
            'email' => 'student_api_2@test.com',
            'password_hash' => password_hash('password', PASSWORD_DEFAULT),
            'full_name' => 'Student API 2',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $studentId = $this->db->insertID();

        // 2. Create Invoice
        $this->db->table('finance_invoices')->insert([
            'school_id' => $this->schoolId,
            'student_id' => $studentId,
            'reference_number' => 'INV-API-002',
            'amount' => 7500.00,
            'balance' => 7500.00,
            'status' => 'unpaid',
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $invoiceId = $this->db->insertID();

        // 3. Call API
        $result = $this->withSession($this->getAdminSession())
                       ->get("/api/finance/invoices/{$invoiceId}");

        // 4. Verify Response
        $result->assertOK();

        $json = json_decode($result->getJSON(), true);
        $this->assertArrayHasKey('data', $json);
        $this->assertEquals('INV-API-002', $json['data']['reference_number']);
        $this->assertEquals('7500.00', $json['data']['amount']);
    }
}
