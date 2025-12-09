<?php

namespace Tests\Feature\Finance;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

/**
 * FinanceCrudTest - Feature tests for Finance Transaction CRUD operations
 * 
 * Tests all CRUD endpoints for the Finance module:
 * - GET /finance/transactions (index)
 * - GET /finance/transactions/create (create form)
 * - POST /finance/transactions/store (create action)
 * - GET /finance/transactions/edit/{id} (edit form)
 * - POST /finance/transactions/update/{id} (update action)
 * - GET /finance/transactions/delete/{id} (delete action)
 */
class FinanceCrudTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use TenantTestTrait;

    protected $migrate = true;
    protected $migrateOnce = false;
    protected $refresh = true;
    protected $namespace = 'App';

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
    }

    /**
     * Test: Index page displays transactions
     */
    public function testIndexDisplaysTransactions()
    {
        // Create a test invoice first
        $this->db->table('finance_invoices')->insert([
            'school_id'        => $this->schoolId,
            'student_id'       => $this->userId,
            'reference_number' => 'INV-TEST-001',
            'amount'           => 10000.00,
            'balance'          => 5000.00,
            'status'           => 'partial',
            'due_date'         => '2025-03-31',
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);
        $invoiceId = $this->db->insertID();

        // Seed a test transaction
        $this->db->table('finance_payments')->insert([
            'school_id'      => $this->schoolId,
            'invoice_id'     => $invoiceId,
            'amount'         => 5000.00,
            'method'         => 'cash',
            'reference_code' => 'CASH-001',
            'paid_at'        => date('Y-m-d H:i:s'),
            'recorded_by'    => $this->userId,
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())
                       ->get('finance/transactions');

        $result->assertOK();
        $result->assertSee('Finance Transactions');
        $result->assertSee('5000');
        $result->assertSee('CASH-001');
    }

    /**
     * Test: Index page shows empty state when no transactions
     */
    public function testIndexShowsEmptyState()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('finance/transactions');

        $result->assertOK();
        $result->assertSee('No transactions found');
    }

    /**
     * Test: Create page displays form
     */
    public function testCreatePageDisplaysForm()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('finance/transactions/create');

        $result->assertOK();
        $result->assertSee('Record Payment');
        $result->assertSee('Amount');
        $result->assertSee('Payment Method');
    }

    /**
     * Test: Store creates a new transaction
     */
    public function testStoreCreatesTransaction()
    {
        // Create a test invoice first
        $this->db->table('finance_invoices')->insert([
            'school_id'        => $this->schoolId,
            'student_id'       => $this->userId,
            'reference_number' => 'INV-TEST-002',
            'amount'           => 10000.00,
            'balance'          => 10000.00,
            'status'           => 'unpaid',
            'due_date'         => '2025-03-31',
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);
        $invoiceId = $this->db->insertID();

        $result = $this->withSession($this->getAdminSession())
                       ->post('finance/transactions/store', [
                           'invoice_id'     => $invoiceId,
                           'amount'         => 5000.00,
                           'method'         => 'mobile_money',
                           'reference_code' => 'MPE-XYZ123',
                           csrf_token()     => csrf_hash(),
                       ]);

        $result->assertRedirectTo('/finance/transactions');
        
        $this->seeInDatabase('finance_payments', [
            'invoice_id'     => $invoiceId,
            'amount'         => 5000.00,
            'method'         => 'mobile_money',
            'reference_code' => 'MPE-XYZ123',
            'school_id'      => $this->schoolId,
        ]);
    }

    /**
     * Test: Store validation fails with missing required fields
     */
    public function testStoreValidationFailsWithMissingFields()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->post('finance/transactions/store', [
                           'invoice_id' => '', // Empty invoice
                           'amount'     => '', // Empty amount
                           csrf_token() => csrf_hash(),
                       ]);

        // Should redirect back with errors
        $result->assertRedirect();
    }

    /**
     * Test: Edit page displays transaction data
     */
    public function testEditPageDisplaysTransactionData()
    {
        // Create a test invoice first
        $this->db->table('finance_invoices')->insert([
            'school_id'        => $this->schoolId,
            'student_id'       => $this->userId,
            'reference_number' => 'INV-TEST-003',
            'amount'           => 10000.00,
            'balance'          => 7000.00,
            'status'           => 'partial',
            'due_date'         => '2025-03-31',
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);
        $invoiceId = $this->db->insertID();

        // Seed a test transaction
        $this->db->table('finance_payments')->insert([
            'school_id'      => $this->schoolId,
            'invoice_id'     => $invoiceId,
            'amount'         => 3000.00,
            'method'         => 'bank_transfer',
            'reference_code' => 'BNK-456',
            'paid_at'        => date('Y-m-d H:i:s'),
            'recorded_by'    => $this->userId,
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
        $transactionId = $this->db->insertID();

        $result = $this->withSession($this->getAdminSession())
                       ->get("finance/transactions/edit/{$transactionId}");

        $result->assertOK();
        $result->assertSee('Edit Transaction');
        $result->assertSee('3000');
        $result->assertSee('BNK-456');
    }

    /**
     * Test: Edit page redirects for non-existent transaction
     */
    public function testEditPageRedirectsForNonExistentTransaction()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('finance/transactions/edit/99999');

        // Should redirect with error
        $result->assertRedirectTo('/finance/transactions');
    }

    /**
     * Test: Update modifies existing transaction
     */
    public function testUpdateModifiesTransaction()
    {
        // Create a test invoice first
        $this->db->table('finance_invoices')->insert([
            'school_id'        => $this->schoolId,
            'student_id'       => $this->userId,
            'reference_number' => 'INV-TEST-004',
            'amount'           => 10000.00,
            'balance'          => 6000.00,
            'status'           => 'partial',
            'due_date'         => '2025-03-31',
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);
        $invoiceId = $this->db->insertID();

        // Seed a test transaction
        $this->db->table('finance_payments')->insert([
            'school_id'      => $this->schoolId,
            'invoice_id'     => $invoiceId,
            'amount'         => 4000.00,
            'method'         => 'cash',
            'reference_code' => 'ORIGINAL-REF',
            'paid_at'        => date('Y-m-d H:i:s'),
            'recorded_by'    => $this->userId,
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
        $transactionId = $this->db->insertID();

        $result = $this->withSession($this->getAdminSession())
                       ->post("finance/transactions/update/{$transactionId}", [
                           'invoice_id'     => $invoiceId,
                           'amount'         => 5000.00,
                           'method'         => 'mobile_money',
                           'reference_code' => 'UPDATED-REF',
                           csrf_token()     => csrf_hash(),
                       ]);

        $result->assertRedirectTo('/finance/transactions');
        
        $this->seeInDatabase('finance_payments', [
            'id'             => $transactionId,
            'amount'         => 5000.00,
            'method'         => 'mobile_money',
            'reference_code' => 'UPDATED-REF',
        ]);
    }

    /**
     * Test: Delete removes a transaction
     */
    public function testDeleteRemovesTransaction()
    {
        // Create a test invoice first
        $this->db->table('finance_invoices')->insert([
            'school_id'        => $this->schoolId,
            'student_id'       => $this->userId,
            'reference_number' => 'INV-TEST-005',
            'amount'           => 10000.00,
            'balance'          => 8000.00,
            'status'           => 'partial',
            'due_date'         => '2025-03-31',
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);
        $invoiceId = $this->db->insertID();

        // Seed a test transaction
        $this->db->table('finance_payments')->insert([
            'school_id'      => $this->schoolId,
            'invoice_id'     => $invoiceId,
            'amount'         => 2000.00,
            'method'         => 'cheque',
            'reference_code' => 'CHQ-789',
            'paid_at'        => date('Y-m-d H:i:s'),
            'recorded_by'    => $this->userId,
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
        $transactionId = $this->db->insertID();

        // Verify transaction exists
        $this->seeInDatabase('finance_payments', ['id' => $transactionId]);

        $result = $this->withSession($this->getAdminSession())
                       ->get("finance/transactions/delete/{$transactionId}");

        $result->assertRedirectTo('/finance/transactions');
        
        // Verify transaction is soft deleted
        $this->dontSeeInDatabase('finance_payments', [
            'id'         => $transactionId,
            'deleted_at' => null,
        ]);
    }

    /**
     * Test: Delete non-existent transaction redirects with error
     */
    public function testDeleteNonExistentTransactionRedirects()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('finance/transactions/delete/99999');

        $result->assertRedirectTo('/finance/transactions');
    }

    /**
     * Test: Tenant scoping - cannot access other school's transactions
     */
    public function testCannotAccessOtherSchoolTransactions()
    {
        // Create an invoice for a different school
        $this->db->table('finance_invoices')->insert([
            'school_id'        => 99999, // Different school
            'student_id'       => 999,
            'reference_number' => 'INV-OTHER-001',
            'amount'           => 10000.00,
            'balance'          => 10000.00,
            'status'           => 'unpaid',
            'due_date'         => '2025-03-31',
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);
        $otherInvoiceId = $this->db->insertID();

        // Create a transaction for a different school
        $this->db->table('finance_payments')->insert([
            'school_id'      => 99999, // Different school
            'invoice_id'     => $otherInvoiceId,
            'amount'         => 1000.00,
            'method'         => 'cash',
            'reference_code' => 'OTHER-SCHOOL',
            'paid_at'        => date('Y-m-d H:i:s'),
            'recorded_by'    => 999,
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
        $otherTransactionId = $this->db->insertID();

        // Try to edit it with our session (different school)
        $result = $this->withSession($this->getAdminSession())
                       ->get("finance/transactions/edit/{$otherTransactionId}");

        // Should redirect because transaction not found for this school
        $result->assertRedirectTo('/finance/transactions');
    }
}
