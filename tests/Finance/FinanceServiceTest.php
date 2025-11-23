<?php

namespace Tests\Finance;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Services\FinanceService;

/**
 * @internal
 */
final class FinanceServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = false;
    protected FinanceService $service;
    protected static bool $migrated = false;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Run migrations only once for all tests
        if (!self::$migrated) {
            $migrate = \Config\Services::migrations();
            $migrate->latest();
            self::$migrated = true;
        }
        
        $this->service = new FinanceService();
        
        // Create minimal test data
        $db = \Config\Database::connect();
        
        // Create school if not exists
        $existing = $db->table('schools')->where('id', 6)->get()->getRow();
        if (!$existing) {
            $db->table('schools')->insert([
                'id' => 6,
                'school_name' => 'Test School',
                'school_code' => 'TEST001',
                'max_students' => 1000,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        
        // Create students if not exist
        $students = [50, 51, 52, 53, 54, 55, 56, 57];
        foreach ($students as $id) {
            $existingUser = $db->table('ci4_users')->where('id', $id)->get()->getRow();
            if (!$existingUser) {
                $db->table('ci4_users')->insert([
                    'id' => $id,
                    'username' => "student{$id}",
                    'email' => "student{$id}@test.com",
                    'full_name' => "Student {$id}",
                    'password_hash' => password_hash('password', PASSWORD_DEFAULT),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    public function testGetSchoolInvoices(): void
    {
        // Create test invoice
        $items = [
            ['name' => 'Tuition Fee', 'amount' => 15000],
            ['name' => 'Activity Fee', 'amount' => 2000],
        ];

        $this->service->createInvoice(50, 6, $items, '2025-12-31'); // Student ID 50 in school 6

        $invoices = $this->service->getSchoolInvoices(6);

        $this->assertIsArray($invoices);
        $this->assertGreaterThan(0, count($invoices));
    }

    public function testCreateInvoice(): void
    {
        $items = [
            ['name' => 'Tuition Fee', 'amount' => 15000],
            ['name' => 'Books', 'amount' => 3000],
        ];

        $result = $this->service->createInvoice(51, 6, $items, '2025-12-31');

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('invoice_id', $result);

        // Verify invoice was created
        $invoice = model('App\Models\InvoiceModel')->find($result['invoice_id']);
        $this->assertEquals(18000, $invoice['total_amount']);
        $this->assertEquals(0, $invoice['paid_amount']);
        $this->assertEquals(18000, $invoice['balance']);
        $this->assertEquals('pending', $invoice['status']);
    }

    public function testRecordPayment(): void
    {
        // Create invoice first
        $items = [['name' => 'Tuition', 'amount' => 10000]];
        $invoiceResult = $this->service->createInvoice(52, 6, $items, '2025-12-31');
        $invoiceId = $invoiceResult['invoice_id'];

        // Record payment
        $result = $this->service->recordPayment($invoiceId, 5000, 'M-Pesa', 'MPESA123');

        $this->assertTrue($result['success']);
        $this->assertEquals(5000, $result['new_balance']);

        // Verify invoice status
        $invoice = model('App\Models\InvoiceModel')->find($invoiceId);
        $this->assertEquals(5000, $invoice['paid_amount']);
        $this->assertEquals(5000, $invoice['balance']);
        $this->assertEquals('partial', $invoice['status']);
    }

    public function testRecordFullPayment(): void
    {
        // Create invoice
        $items = [['name' => 'Tuition', 'amount' => 8000]];
        $invoiceResult = $this->service->createInvoice(53, 6, $items, '2025-12-31');
        $invoiceId = $invoiceResult['invoice_id'];

        // Record full payment
        $result = $this->service->recordPayment($invoiceId, 8000, 'Cash', null);

        $this->assertTrue($result['success']);
        $this->assertEquals(0, $result['new_balance']);

        // Verify invoice status
        $invoice = model('App\Models\InvoiceModel')->find($invoiceId);
        $this->assertEquals(8000, $invoice['paid_amount']);
        $this->assertEquals(0, $invoice['balance']);
        $this->assertEquals('paid', $invoice['status']);
    }

    public function testGetInvoiceDetails(): void
    {
        // Create invoice
        $items = [['name' => 'Tuition', 'amount' => 12000]];
        $invoiceResult = $this->service->createInvoice(54, 6, $items, '2025-12-31');
        $invoiceId = $invoiceResult['invoice_id'];

        // Record payment
        $this->service->recordPayment($invoiceId, 6000, 'M-Pesa', 'MPESA456');

        // Get details
        $details = $this->service->getInvoiceDetails($invoiceId);

        $this->assertNotNull($details);
        $this->assertArrayHasKey('payments', $details);
        $this->assertArrayHasKey('items', $details);
        $this->assertCount(1, $details['payments']);
        $this->assertIsArray($details['items']);
    }

    public function testGetPaymentStats(): void
    {
        // Create and pay multiple invoices
        for ($i = 0; $i < 3; $i++) {
            $items = [['name' => 'Tuition', 'amount' => 10000]];
            $invoiceResult = $this->service->createInvoice(55 + $i, 6, $items, '2025-12-31');
            $this->service->recordPayment($invoiceResult['invoice_id'], 5000, 'M-Pesa', 'MPESA' . $i);
        }

        $stats = $this->service->getPaymentStats(6);

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_collected', $stats);
        $this->assertArrayHasKey('payment_count', $stats);
        $this->assertArrayHasKey('by_method', $stats);
        $this->assertGreaterThan(0, $stats['total_collected']);
    }

    public function testGetOutstandingInvoices(): void
    {
        // Create some invoices with outstanding balances
        $items = [['name' => 'Tuition', 'amount' => 20000]];
        $this->service->createInvoice(58, 6, $items, '2025-12-31');
        $this->service->createInvoice(59, 6, $items, '2025-12-31');

        $outstanding = $this->service->getOutstandingInvoices(6);

        $this->assertIsArray($outstanding);
        $this->assertGreaterThan(0, count($outstanding));

        foreach ($outstanding as $invoice) {
            $this->assertGreaterThan(0, $invoice['balance']);
        }
    }

    public function testGetFeeStructure(): void
    {
        // Set fee structure first
        $feeItems = [
            ['name' => 'Tuition', 'amount' => 15000],
            ['name' => 'Activity Fee', 'amount' => 2000],
        ];

        $this->service->setFeeStructure(6, 'Grade 1', $feeItems);

        $structure = $this->service->getFeeStructure(6, 'Grade 1');

        $this->assertIsArray($structure);
        $this->assertGreaterThan(0, count($structure));
    }

    public function testSetFeeStructure(): void
    {
        $feeItems = [
            ['name' => 'Tuition', 'amount' => 18000],
            ['name' => 'Sports Fee', 'amount' => 3000],
        ];

        $result = $this->service->setFeeStructure(6, 'Grade 2', $feeItems);

        $this->assertTrue($result['success']);

        // Verify fee structure
        $structure = $this->service->getFeeStructure(6, 'Grade 2');
        $this->assertCount(1, $structure);
    }

    public function testUpdateFeeStructure(): void
    {
        $feeItems = [
            ['name' => 'Tuition', 'amount' => 16000],
        ];

        // Set initial
        $this->service->setFeeStructure(6, 'Grade 3', $feeItems);

        // Update
        $newFeeItems = [
            ['name' => 'Tuition', 'amount' => 17000],
            ['name' => 'Lab Fee', 'amount' => 4000],
        ];

        $result = $this->service->setFeeStructure(6, 'Grade 3', $newFeeItems);

        $this->assertTrue($result['success']);
    }
}
