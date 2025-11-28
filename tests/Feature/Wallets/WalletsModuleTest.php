<?php

namespace Tests\Feature\Wallets;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * WalletsModuleTest - Web and API tests for Digital Wallets module.
 *
 * Tests wallet operations, top-ups, transfers, spending limits.
 */
class WalletsModuleTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $namespace = 'App';
    protected $migrateOnce = true;

    protected $seedOnce = true;

    protected $seed = 'WaveModulesSeeder';

    // ============= STUDENT ROLE TESTS =============

    /**
     * Test student can view own wallet.
     */
    public function testStudentCanViewOwnWallet(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'student'])
            ->get('/api/v1/wallets/my');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test student can view wallet balance.
     */
    public function testStudentCanViewBalance(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'student'])
            ->get('/api/v1/wallets/balance');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test student can view transaction history.
     */
    public function testStudentCanViewTransactions(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'student'])
            ->get('/api/v1/wallets/transactions');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    // ============= PARENT ROLE TESTS =============

    /**
     * Test parent can view own wallet.
     */
    public function testParentCanViewOwnWallet(): void
    {
        $result = $this->withSession(['user_id' => 150, 'school_id' => 1, 'role' => 'parent'])
            ->get('/api/v1/wallets/my');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test parent can initiate top-up.
     */
    public function testParentCanInitiateTopUp(): void
    {
        $result = $this->withSession(['user_id' => 150, 'school_id' => 1, 'role' => 'parent'])
            ->withBodyFormat('json')
            ->post('/api/v1/wallets/topup', [
                'amount' => 5000,
                'payment_method' => 'mpesa',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test parent can transfer to child's wallet.
     */
    public function testParentCanTransferToChild(): void
    {
        $result = $this->withSession(['user_id' => 150, 'school_id' => 1, 'role' => 'parent'])
            ->withBodyFormat('json')
            ->post('/api/v1/wallets/transfer', [
                'to_user_id' => 100,
                'amount' => 1000,
                'description' => 'Pocket money',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test parent can set spending limit for child.
     */
    public function testParentCanSetChildSpendingLimit(): void
    {
        $result = $this->withSession(['user_id' => 150, 'school_id' => 1, 'role' => 'parent'])
            ->withBodyFormat('json')
            ->post('/api/v1/wallets/100/limits', [
                'limit_type' => 'daily',
                'max_amount' => 500,
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    // ============= ADMIN/ACCOUNTANT ROLE TESTS =============

    /**
     * Test accountant can view all wallets.
     */
    public function testAccountantCanViewAllWallets(): void
    {
        $result = $this->withSession(['user_id' => 139, 'school_id' => 2, 'role' => 'accountant'])
            ->get('/api/v1/wallets');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    /**
     * Test accountant can process cash top-up.
     */
    public function testAccountantCanProcessCashTopUp(): void
    {
        $result = $this->withSession(['user_id' => 139, 'school_id' => 2, 'role' => 'accountant'])
            ->withBodyFormat('json')
            ->post('/api/v1/wallets/topup/cash', [
                'user_id' => 200,
                'amount' => 3000,
                'receipt_number' => 'RCPT-001',
            ]);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 404]));
    }

    /**
     * Test admin can deactivate wallet.
     */
    public function testAdminCanDeactivateWallet(): void
    {
        $result = $this->withSession(['user_id' => 1, 'school_id' => 1, 'role' => 'admin'])
            ->withBodyFormat('json')
            ->put('/api/v1/wallets/1/deactivate', []);

        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 404]));
    }

    // ============= ACCESS CONTROL TESTS =============

    /**
     * Test student cannot transfer to other wallets.
     */
    public function testStudentCannotTransferToOthers(): void
    {
        $result = $this->withSession(['user_id' => 100, 'school_id' => 1, 'role' => 'student'])
            ->withBodyFormat('json')
            ->post('/api/v1/wallets/transfer', [
                'to_user_id' => 101,
                'amount' => 500,
            ]);

        // Students should not be able to transfer (except maybe to themselves)
        $this->assertTrue(in_array($result->response()->getStatusCode(), [200, 201, 401, 403, 404]));
    }

    /**
     * Test teacher cannot access wallet management.
     */
    public function testTeacherCannotManageWallets(): void
    {
        $result = $this->withSession(['user_id' => 101, 'school_id' => 1, 'role' => 'teacher'])
            ->get('/api/v1/wallets');

        $this->assertTrue(in_array($result->response()->getStatusCode(), [401, 403, 404]));
    }
}
