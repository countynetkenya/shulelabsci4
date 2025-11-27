<?php

namespace Tests\Feature\Finance;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;

class FinanceTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = false;
    protected $refresh     = true;
    protected $namespace   = 'App'; // Or null to run all

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testApiListInvoicesReturnsOk()
    {
        // We expect this to eventually return 200
        $result = $this->call('get', 'api/finance/invoices/1');
        $result->assertStatus(200);
    }

    public function testWebDashboardReturnsOk()
    {
        // We expect this to eventually return 200
        $result = $this->call('get', 'finance');
        $result->assertStatus(200);
    }
}
