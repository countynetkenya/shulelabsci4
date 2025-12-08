<?php

namespace Tests\Finance;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\Traits\TenantTestTrait;

class PaymentsTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;
    use TenantTestTrait;

    protected $migrate = true;
    protected $migrateOnce = false;
    protected $refresh = true;
    protected $namespace = 'App';

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
        
        // Robust CSRF Disable
        $config = config('Filters');
        $newBefore = [];
        foreach ($config->globals['before'] as $key => $value) {
            if ($value !== 'csrf' && $key !== 'csrf') {
                if (is_array($value)) {
                     $newBefore[$key] = $value;
                } else {
                    $newBefore[] = $value;
                }
            }
        }
        $config->globals['before'] = $newBefore;
        \CodeIgniter\Config\Factories::injectMock('filters', 'Filters', $config);
    }

    public function testIndex()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->call('get', '/finance/payments');
        $result->assertOK();
        $result->assertSee('Payments');
    }

    public function testCreate()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->call('get', '/finance/payments/create');
        $result->assertOK();
        $result->assertSee('Record Payment');
    }

    public function testStore()
    {
        // Create a student
        $this->db->table('users')->insert([
            'username' => 'student2',
            'email' => 'student2@example.com',
            'password_hash' => 'hash',
            'full_name' => 'Student Two',
            'is_active' => 1
        ]);
        $studentId = $this->db->insertID();

        // Create an invoice
        $this->db->table('finance_invoices')->insert([
            'school_id' => $this->schoolId,
            'student_id' => $studentId,
            'reference_number' => 'INV-TEST-001',
            'amount' => 5000,
            'balance' => 5000,
            'status' => 'unpaid',
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        $invoiceId = $this->db->insertID();

        $result = $this->withSession($this->getAdminSession())
                       ->call('post', '/finance/payments', [
            'invoice_id' => $invoiceId,
            'amount' => 2000,
            'payment_method' => 'cash',
            'reference_number' => 'PAY-TEST-001'
        ]);

        $result->assertRedirectTo('/finance/invoices');
        $this->seeInDatabase('finance_payments', ['invoice_id' => $invoiceId, 'amount' => 2000]);
        
        // Verify invoice balance updated
        $this->seeInDatabase('finance_invoices', ['id' => $invoiceId, 'balance' => 3000]);
    }
}
